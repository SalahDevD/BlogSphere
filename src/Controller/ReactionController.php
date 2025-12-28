<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Reaction;
use App\Entity\CommentReaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reaction', name: 'reaction_')]
class ReactionController extends AbstractController
{
    /**
     * 👍 Liker un article - NÉCESSITE AUTHENTIFICATION
     * - Si déjà liké : retire le like
     * - Si disliké : change en like
     * - Sinon : ajoute un like
     */
    #[Route('/article/{articleId}/like', name: 'article_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function likeArticle(int $articleId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $article = $em->getRepository(Article::class)->find($articleId);
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        // Vérifier que l'article est approuvé
        if ($article->getValidationStatus() !== 'approved') {
            return $this->json(['error' => 'Impossible de réagir à un article non publié'], 403);
        }

        $reactionRepo = $em->getRepository(Reaction::class);

        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'article' => $article,
        ]);

        if ($existingReaction) {
            if ($existingReaction->isIsLike()) {
                // Retirer le like
                $em->remove($existingReaction);
                $message = 'Like retiré';
            } else {
                // Changer dislike en like
                $existingReaction->setIsLike(true);
                $message = 'Changé en like';
            }
        } else {
            // Nouvelle réaction
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setArticle($article);
            $reaction->setIsLike(true);
            $reaction->setCreatedAt(new \DateTime());

            $em->persist($reaction);
            $message = 'Article liké';
        }

        $em->flush();

        // Compter les likes/dislikes
        $likesCount = $reactionRepo->count(['article' => $article, 'isLike' => true]);
        $dislikesCount = $reactionRepo->count(['article' => $article, 'isLike' => false]);

        return $this->json([
            'message'   => $message,
            'likes'     => $likesCount,
            'dislikes'  => $dislikesCount,
        ]);
    }

    /**
     * 👎 Disliker un article - NÉCESSITE AUTHENTIFICATION
     * - Si déjà disliké : retire le dislike
     * - Si liké : change en dislike
     * - Sinon : ajoute un dislike
     */
    #[Route('/article/{articleId}/dislike', name: 'article_dislike', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function dislikeArticle(int $articleId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $article = $em->getRepository(Article::class)->find($articleId);
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }

        // Vérifier que l'article est approuvé
        if ($article->getValidationStatus() !== 'approved') {
            return $this->json(['error' => 'Impossible de réagir à un article non publié'], 403);
        }

        $reactionRepo = $em->getRepository(Reaction::class);

        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'article' => $article,
        ]);

        if ($existingReaction) {
            if (!$existingReaction->isIsLike()) {
                // Retirer le dislike
                $em->remove($existingReaction);
                $message = 'Dislike retiré';
            } else {
                // Changer like en dislike
                $existingReaction->setIsLike(false);
                $message = 'Changé en dislike';
            }
        } else {
            // Nouvelle réaction
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setArticle($article);
            $reaction->setIsLike(false);
            $reaction->setCreatedAt(new \DateTime());

            $em->persist($reaction);
            $message = 'Article disliké';
        }

        $em->flush();

        $likesCount = $reactionRepo->count(['article' => $article, 'isLike' => true]);
        $dislikesCount = $reactionRepo->count(['article' => $article, 'isLike' => false]);

        return $this->json([
            'message'   => $message,
            'likes'     => $likesCount,
            'dislikes'  => $dislikesCount,
        ]);
    }

    /**
     * 👍 Liker un commentaire - NÉCESSITE AUTHENTIFICATION
     */
    #[Route('/comment/{commentId}/like', name: 'comment_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function likeComment(int $commentId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $comment = $em->getRepository(Comment::class)->find($commentId);
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], 404);
        }

        $reactionRepo = $em->getRepository(CommentReaction::class);

        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'comment' => $comment,
        ]);

        if ($existingReaction) {
            if ($existingReaction->isIsLike()) {
                $em->remove($existingReaction);
                $message = 'Like retiré';
            } else {
                $existingReaction->setIsLike(true);
                $message = 'Changé en like';
            }
        } else {
            $reaction = new CommentReaction();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setIsLike(true);
            $reaction->setCreatedAt(new \DateTime());

            $em->persist($reaction);
            $message = 'Commentaire liké';
        }

        $em->flush();

        $likesCount = $reactionRepo->count(['comment' => $comment, 'isLike' => true]);
        $dislikesCount = $reactionRepo->count(['comment' => $comment, 'isLike' => false]);

        return $this->json([
            'message'   => $message,
            'likes'     => $likesCount,
            'dislikes'  => $dislikesCount,
        ]);
    }

    /**
     * 👎 Disliker un commentaire - NÉCESSITE AUTHENTIFICATION
     */
    #[Route('/comment/{commentId}/dislike', name: 'comment_dislike', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function dislikeComment(int $commentId, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $comment = $em->getRepository(Comment::class)->find($commentId);
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], 404);
        }

        $reactionRepo = $em->getRepository(CommentReaction::class);

        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'comment' => $comment,
        ]);

        if ($existingReaction) {
            if (!$existingReaction->isIsLike()) {
                $em->remove($existingReaction);
                $message = 'Dislike retiré';
            } else {
                $existingReaction->setIsLike(false);
                $message = 'Changé en dislike';
            }
        } else {
            $reaction = new CommentReaction();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setIsLike(false);
            $reaction->setCreatedAt(new \DateTime());

            $em->persist($reaction);
            $message = 'Commentaire disliké';
        }

        $em->flush();

        $likesCount = $reactionRepo->count(['comment' => $comment, 'isLike' => true]);
        $dislikesCount = $reactionRepo->count(['comment' => $comment, 'isLike' => false]);

        return $this->json([
            'message'   => $message,
            'likes'     => $likesCount,
            'dislikes'  => $dislikesCount,
        ]);
    }
}
