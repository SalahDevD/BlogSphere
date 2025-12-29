<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentReaction;
use App\Entity\Reaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reaction', name: 'app_reaction_')]
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
            $this->addFlash('error', '❌ Article non trouvé');
            return $this->redirectToRoute('app_article_index');
        }

        if ($article->getValidationStatus() !== 'approved') {
            $this->addFlash('error', '❌ Impossible de réagir à un article non publié');
            return $this->redirectToRoute('app_article_show', ['id' => $articleId]);
        }

        $reactionRepo = $em->getRepository(Reaction::class);
        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'article' => $article,
        ]);

        if ($existingReaction) {
            if ($existingReaction->isLike()) {
                $em->remove($existingReaction);
                $this->addFlash('success', '👍 Like retiré');
            } else {
                $existingReaction->setIsLike(true);
                $this->addFlash('success', '👍 Changé en like');
            }
        } else {
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setArticle($article);
            $reaction->setIsLike(true);
            $reaction->setCreatedAt(new \DateTime());
            $em->persist($reaction);
            $this->addFlash('success', '👍 Article liké');
        }

        $em->flush();
        return $this->redirectToRoute('app_article_show', ['id' => $articleId]);
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
            $this->addFlash('error', '❌ Article non trouvé');
            return $this->redirectToRoute('app_article_index');
        }

        if ($article->getValidationStatus() !== 'approved') {
            $this->addFlash('error', '❌ Impossible de réagir à un article non publié');
            return $this->redirectToRoute('app_article_show', ['id' => $articleId]);
        }

        $reactionRepo = $em->getRepository(Reaction::class);
        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'article' => $article,
        ]);

        if ($existingReaction) {
            if (!$existingReaction->isLike()) {
                $em->remove($existingReaction);
                $this->addFlash('success', '👎 Dislike retiré');
            } else {
                $existingReaction->setIsLike(false);
                $this->addFlash('success', '👎 Changé en dislike');
            }
        } else {
            $reaction = new Reaction();
            $reaction->setUser($user);
            $reaction->setArticle($article);
            $reaction->setIsLike(false);
            $reaction->setCreatedAt(new \DateTime());
            $em->persist($reaction);
            $this->addFlash('success', '👎 Article disliké');
        }

        $em->flush();
        return $this->redirectToRoute('app_article_show', ['id' => $articleId]);
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
            $this->addFlash('error', '❌ Commentaire non trouvé');
            return $this->redirectToRoute('app_article_index');
        }

        $reactionRepo = $em->getRepository(CommentReaction::class);
        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'comment' => $comment,
        ]);

        if ($existingReaction) {
            if ($existingReaction->isLike()) {
                $em->remove($existingReaction);
                $this->addFlash('success', '👍 Like retiré');
            } else {
                $existingReaction->setIsLike(true);
                $this->addFlash('success', '👍 Changé en like');
            }
        } else {
            $reaction = new CommentReaction();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setIsLike(true);
            $reaction->setCreatedAt(new \DateTime());
            $em->persist($reaction);
            $this->addFlash('success', '👍 Commentaire liké');
        }

        $em->flush();
        return $this->redirectToRoute('app_article_show', ['id' => $comment->getArticle()->getId()]);
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
            $this->addFlash('error', '❌ Commentaire non trouvé');
            return $this->redirectToRoute('app_article_index');
        }

        $reactionRepo = $em->getRepository(CommentReaction::class);
        $existingReaction = $reactionRepo->findOneBy([
            'user'    => $user,
            'comment' => $comment,
        ]);

        if ($existingReaction) {
            if (!$existingReaction->isLike()) {
                $em->remove($existingReaction);
                $this->addFlash('success', '👎 Dislike retiré');
            } else {
                $existingReaction->setIsLike(false);
                $this->addFlash('success', '👎 Changé en dislike');
            }
        } else {
            $reaction = new CommentReaction();
            $reaction->setUser($user);
            $reaction->setComment($comment);
            $reaction->setIsLike(false);
            $reaction->setCreatedAt(new \DateTime());
            $em->persist($reaction);
            $this->addFlash('success', '👎 Commentaire disliké');
        }

        $em->flush();
        return $this->redirectToRoute('app_article_show', ['id' => $comment->getArticle()->getId()]);
    }
}
