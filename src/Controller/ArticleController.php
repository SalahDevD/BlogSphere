<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\ReactionRepository;
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
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(
            ['validationStatus' => 'approved'],
            ['createdAt' => 'DESC']
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
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

            $article->setAuthor($this->getUser());
            $article->setValidationStatus('pending');
            $article->setCreatedAt(new \DateTime());
            $em->persist($article);
            $em->flush();

            $this->addFlash('info', '📨 Votre article a été soumis et est en attente de validation.');
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
    public function edit(Request $request, Article $article, EntityManagerInterface $em): Response
    {
        if ($article->getAuthor() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(ArticleType::class, $article);
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
}
