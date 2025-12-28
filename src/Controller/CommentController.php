<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments', name: 'app_comment_')]
class CommentController extends AbstractController
{
    /**
     * 💬 Création de commentaire via API JSON
     * POST /comments/api/create
     * Body JSON : { "articleId": 123, "content": "..." }
     */
    #[Route('/api/create', name: 'api_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function apiCreate(
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $articleId = $data['articleId'] ?? null;
        $content   = $data['content'] ?? null;

        if (!$articleId || !$content) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $article = $articleRepository->find($articleId);
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        // Vérifier que l'article est approuvé
        if ($article->getValidationStatus() !== 'approved') {
            return $this->json(['error' => 'Impossible de commenter un article non publié'], 403);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($user);
        $comment->setArticle($article);
        $comment->setCreatedAt(new \DateTime());

        $em->persist($comment);
        $em->flush();

        return $this->json([
            'message' => 'Commentaire créé avec succès',
            'id'      => $comment->getId(),
        ], 201);
    }

    /**
     * 💬 Ajouter un commentaire depuis le formulaire HTML de l’article
     * POST /comments/add/{id}
     */
    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(
        Article $article,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user    = $this->getUser();
        $content = trim((string) $request->request->get('content'));

        if ($content === '') {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        // Vérifier que l'article est approuvé
        if ($article->getValidationStatus() !== 'approved') {
            $this->addFlash('error', 'Impossible de commenter un article non publié.');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($user);
        $comment->setArticle($article);
        $comment->setCreatedAt(new \DateTime());

        $em->persist($comment);
        $em->flush();

        $this->addFlash('success', '💬 Commentaire ajouté avec succès.');

        return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
    }

    /**
     * 🗑️ Supprimer un commentaire - NÉCESSITE AUTHENTIFICATION
     * L'auteur ou le superviseur peut supprimer
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Comment $comment, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Seul l'auteur ou superviseur peut supprimer
        if ($comment->getAuthor() !== $user && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $em->remove($comment);
        $em->flush();

        return $this->json(['message' => 'Commentaire supprimé avec succès']);
    }
}
