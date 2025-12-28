<?php

namespace App\Controller;

use App\Entity\CommentReaction;
use App\Entity\Comment;
use App\Repository\CommentReactionRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/comment-reactions', name: 'api_comment_reactions')]
class CommentReactionController extends AbstractController
{
    #[Route('/like/{commentId}', name: 'comment_reaction_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function likeComment(
        int $commentId,
        CommentRepository $commentRepository,
        CommentReactionRepository $reactionRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $comment = $commentRepository->find($commentId);
        
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur a déjà réagi
        $existingReaction = $reactionRepository->findOneBy([
            'user' => $user,
            'comment' => $comment
        ]);
        
        if ($existingReaction) {
            if ($existingReaction->getType() === 'like') {
                $em->remove($existingReaction);
                $em->flush();
                return $this->json(['message' => 'Like retiré'], 200);
            }
            $existingReaction->setType('like');
            $em->flush();
            return $this->json(['message' => 'Réaction modifiée en like'], 200);
        }
        
        $reaction = new CommentReaction();
        $reaction->setUser($user);
        $reaction->setComment($comment);
        $reaction->setType('like');
        $reaction->setCreatedAt(new \DateTime());
        
        $em->persist($reaction);
        $em->flush();
        
        return $this->json(['message' => 'Like sur commentaire ajouté'], 201);
    }
    
    #[Route('/dislike/{commentId}', name: 'comment_reaction_dislike', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function dislikeComment(
        int $commentId,
        CommentRepository $commentRepository,
        CommentReactionRepository $reactionRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $comment = $commentRepository->find($commentId);
        
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur a déjà réagi
        $existingReaction = $reactionRepository->findOneBy([
            'user' => $user,
            'comment' => $comment
        ]);
        
        if ($existingReaction) {
            if ($existingReaction->getType() === 'dislike') {
                $em->remove($existingReaction);
                $em->flush();
                return $this->json(['message' => 'Dislike retiré'], 200);
            }
            $existingReaction->setType('dislike');
            $em->flush();
            return $this->json(['message' => 'Réaction modifiée en dislike'], 200);
        }
        
        $reaction = new CommentReaction();
        $reaction->setUser($user);
        $reaction->setComment($comment);
        $reaction->setType('dislike');
        $reaction->setCreatedAt(new \DateTime());
        
        $em->persist($reaction);
        $em->flush();
        
        return $this->json(['message' => 'Dislike sur commentaire ajouté'], 201);
    }
}
