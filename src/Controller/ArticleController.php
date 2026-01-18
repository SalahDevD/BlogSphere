<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ReactionRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/articles', name: 'app_article')]
class ArticleController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger
    ) {
    }

    #[Route('', name: '_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository, Request $request): Response
    {
        $categoryId = $request->query->get('category');
        $searchQuery = $request->query->get('search');
        
        if ($searchQuery) {
            // Search by title or content
            $articles = $articleRepository->createQueryBuilder('a')
                ->where('a.validationStatus = :status')
                ->andWhere('(a.title LIKE :search OR a.content LIKE :search)')
                ->setParameter('status', 'approved')
                ->setParameter('search', '%' . $searchQuery . '%')
                ->orderBy('a.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        } elseif ($categoryId) {
            $category = $categoryRepository->find($categoryId);
            if ($category) {
                $articles = $articleRepository->findBy(
                    ['validationStatus' => 'approved', 'category' => $category],
                    ['createdAt' => 'DESC']
                );
            } else {
                $articles = [];
            }
        } else {
            $articles = $articleRepository->findBy(
                ['validationStatus' => 'approved'],
                ['createdAt' => 'DESC']
            );
        }

        $categories = $categoryRepository->findAll();
        $selectedCategory = $categoryId ? $categoryRepository->find($categoryId) : null;

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, ArticleRepository $articleRepository, TagRepository $tagRepository): Response
    {
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur a un article approuvé pour pouvoir créer d'autres articles directement
        $hasApprovedArticle = $articleRepository->count([
            'author' => $user,
            'validationStatus' => 'approved'
        ]) > 0;

        // Si on est en GET et qu'il n'a pas d'article approuvé ET qu'il a un article pending, rediriger avec message
        if ($request->getMethod() === 'GET') {
            $pendingCount = $articleRepository->count([
                'author' => $user,
                'validationStatus' => 'pending'
            ]);
            
            if (!$hasApprovedArticle && $pendingCount > 0) {
                $this->addFlash('warning', '⏳ Vous avez un premier article en attente de validation. Une fois approuvé, vous pourrez créer d\'autres articles.');
                return $this->redirectToRoute('user_profile_index');
            }
        }

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = 'article_' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move(
                    $this->getParameter('articles_images_directory'),
                    $newFilename
                );

                $article->setImage($newFilename);
            }

            // Process tags from comma-separated string
            $tagsString = $form->get('tags')->getData();
            if ($tagsString) {
                $this->processTags($article, $tagsString, $tagRepository, $em);
            }

            $article->setAuthor($user);
            $article->setAuthorName($user->getName());
            $article->setCreatedAt(new \DateTime());

            // Vérifier si c'est le premier article APPROUVÉ de l'utilisateur
            if (!$hasApprovedArticle) {
                // Premier article: mise en attente de validation du superviseur
                $article->setValidationStatus('pending');
                $message = '📨 Votre premier article a été soumis et est en attente de validation par un superviseur. Une fois validé, vous pourrez publier directement vos prochains articles.';
            } else {
                // Articles suivants: publié directement
                $article->setValidationStatus('approved');
                $message = '✅ Votre article a été publié avec succès !';
            }

            $em->persist($article);
            $em->flush();

            $this->addFlash('info', $message);
            return $this->redirectToRoute('app_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: '_show', methods: ['GET'], priority: -1)]
    public function show(Article $article, ReactionRepository $reactionRepo): Response
    {
        if ($article->getValidationStatus() !== 'approved') {
            $user = $this->getUser();
            if (
                !$user ||
                ($article->getAuthor() !== $user &&
                 !$this->isGranted('ROLE_SUPERVISOR') &&
                 !$this->isGranted('ROLE_ADMIN'))
            ) {
                $this->addFlash('danger', '❌ Cet article n\'est pas encore publié.');
                return $this->redirectToRoute('app_article_index');
            }
        }

        // Compter les likes et dislikes
        $likesCount = $reactionRepo->countLikesForArticle($article->getId());
        $dislikesCount = $reactionRepo->countDislikesForArticle($article->getId());

        // Récupérer la réaction de l'utilisateur connecté
        $userReaction = null;
        if ($this->getUser()) {
            $userReaction = $reactionRepo->findUserReactionForArticle(
                $this->getUser()->getId(),
                $article->getId()
            );
        }

        return $this->render('article/show.html.twig', [
            'article'       => $article,
            'likesCount'    => $likesCount,
            'dislikesCount' => $dislikesCount,
            'userReaction'  => $userReaction,
        ]);
    }

    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Article $article, EntityManagerInterface $em, TagRepository $tagRepository): Response
    {
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(ArticleType::class, $article);
        
        // Préremplir les tags existants en chaîne séparée par des virgules
        if ($article->getTags()->count() > 0) {
            $tagsString = implode(', ', $article->getTags()->map(function($tag) {
                return $tag->getName();
            })->toArray());
            $form->get('tags')->setData($tagsString);
        }
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = 'article_' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('articles_images_directory'),
                    $newFilename
                );
                $article->setImage($newFilename);
            }

            // Process tags from comma-separated string
            $tagsString = $form->get('tags')->getData();
            if ($tagsString !== null) {
                // Clear existing tags
                $article->getTags()->clear();
                // Add new tags
                $this->processTags($article, $tagsString, $tagRepository, $em);
            }

            $article->setUpdatedAt(new \DateTime());
            $em->flush();

            $this->addFlash('success', '✅ Article modifié avec succès.');
            return $this->redirectToRoute('app_article_show', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: '_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('success', '✅ Article supprimé avec succès.');
        }

        return $this->redirectToRoute('app_article_index');
    }

    /**
     * Process comma-separated tags string and attach to article
     */
    private function processTags(Article $article, string $tagsString, TagRepository $tagRepository, EntityManagerInterface $em): void
    {
        if (empty($tagsString)) {
            return;
        }

        // Split by comma and clean up
        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagNames = array_filter($tagNames); // Remove empty strings

        foreach ($tagNames as $tagName) {
            // Look for existing tag (case-insensitive)
            $tag = $tagRepository->findOneBy(['name' => $tagName]);

            if (!$tag) {
                // Create new tag
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setSlug(strtolower(str_replace(' ', '-', $tagName)));
                $em->persist($tag);
            }

            // Add tag to article if not already present
            if (!$article->getTags()->contains($tag)) {
                $article->addTag($tag);
            }
        }
    }
}
