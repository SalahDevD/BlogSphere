<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/articles', name: 'api_articles')]
class ArticleManagementController extends AbstractController
{
    #[Route('/create', name: 'article_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        // Validation des données
        if (!isset($data['title']) || !isset($data['content'])) {
            return $this->json(['error' => 'Titre et contenu requis'], 400);
        }
        
        // Vérifier si c'est le premier article PUBLIÉ de l'utilisateur
        $publishedArticlesCount = $articleRepository->count([
            'author' => $user,
            'status' => 'PUBLISHED'
        ]);
        
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setAuthor($user);
        $article->setAuthorName($user->getName());
        $article->setCreatedAt(new \DateTime());
        
        // Premier article = PENDING (nécessite validation superviseur)
        // Articles suivants = PUBLISHED directement
        if ($publishedArticlesCount === 0) {
            $article->setStatus('PENDING');
            $message = 'Votre premier article a été soumis et est en attente de validation par un superviseur';
        } else {
            $article->setStatus('PUBLISHED');
            $article->setPublishedAt(new \DateTime());
            $message = 'Article publié avec succès';
        }
        
        // Gestion des catégories et tags si fournis
        if (isset($data['categoryId'])) {
            $category = $em->getRepository(\App\Entity\Category::class)->find($data['categoryId']);
            if ($category) {
                $article->setCategory($category);
            }
        }
        
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagId) {
                $tag = $em->getRepository(\App\Entity\Tag::class)->find($tagId);
                if ($tag) {
                    $article->addTag($tag);
                }
            }
        }
        
        $em->persist($article);
        $em->flush();
        
        return $this->json([
            'message' => $message,
            'id' => $article->getId(),
            'status' => $article->getStatus(),
            'requiresValidation' => $publishedArticlesCount === 0
        ], 201);
    }
    
    #[Route('/{id}/edit', name: 'article_edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $article = $articleRepository->find($id);
        
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }
        
        // Seul l'auteur peut modifier son article
        if ($article->getAuthor() !== $user && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }
        
        $article->setUpdatedAt(new \DateTime());
        $em->flush();
        
        return $this->json(['message' => 'Article modifié avec succès']);
    }
    
    #[Route('/{id}/delete', name: 'article_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        int $id,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $article = $articleRepository->find($id);
        
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }
        
        // Seul l'auteur peut supprimer son article
        if ($article->getAuthor() !== $user && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }
        
        $em->remove($article);
        $em->flush();
        
        return $this->json(['message' => 'Article supprimé avec succès']);
    }
}
