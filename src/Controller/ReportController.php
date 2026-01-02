<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Report;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/reports', name: 'api_reports_')]
class ReportController extends AbstractController
{
    /**
     * 🚩 Signaler un article - NÉCESSITE AUTHENTIFICATION
     * Le signalement est envoyé automatiquement au superviseur
     */
    #[Route('/article/{articleId}', name: 'article', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reportArticle(
        int $articleId,
        Request $request,
        EntityManagerInterface $em,
        ReportRepository $reportRepository
    ): Response {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        $reason = $data['reason'] ?? null;
        
        if (!$reason) {
            return $this->json(['error' => 'La raison du signalement est requise'], 400);
        }
        
        $article = $em->getRepository(Article::class)->find($articleId);
        
        if (!$article) {
            return $this->json(['error' => 'Article non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur a déjà signalé cet article
        $existingReport = $reportRepository->findOneBy([
            'reporter' => $user,
            'article' => $article
        ]);
        
        if ($existingReport) {
            return $this->json(['error' => 'Vous avez déjà signalé cet article'], 400);
        }
        
        // Créer le signalement et l'envoyer au superviseur
        $report = new Report();
        $report->setReporter($user);
        $report->setArticle($article);
        $report->setReason($reason);
        $report->setStatus('PENDING');
        $report->setCreatedAt(new \DateTime());
        
        $em->persist($report);
        $em->flush();
        
        return $this->json([
            'message' => '✅ Article signalé avec succès. Le superviseur sera notifié.'
        ], 201);
    }
    
    /**
     * 🚩 Signaler un commentaire - NÉCESSITE AUTHENTIFICATION
     * Le signalement est envoyé automatiquement au superviseur
     */
    #[Route('/comment/{commentId}', name: 'comment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reportComment(
        int $commentId,
        Request $request,
        EntityManagerInterface $em,
        ReportRepository $reportRepository
    ): Response {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        $reason = $data['reason'] ?? null;
        
        if (!$reason) {
            return $this->json(['error' => 'La raison du signalement est requise'], 400);
        }
        
        $comment = $em->getRepository(Comment::class)->find($commentId);
        
        if (!$comment) {
            return $this->json(['error' => 'Commentaire non trouvé'], 404);
        }
        
        // Vérifier si l'utilisateur a déjà signalé ce commentaire
        $existingReport = $reportRepository->findOneBy([
            'reporter' => $user,
            'comment' => $comment
        ]);
        
        if ($existingReport) {
            return $this->json(['error' => 'Vous avez déjà signalé ce commentaire'], 400);
        }
        
        // Créer le signalement et l'envoyer au superviseur
        $report = new Report();
        $report->setReporter($user);
        $report->setComment($comment);
        $report->setReason($reason);
        $report->setStatus('PENDING');
        $report->setCreatedAt(new \DateTime());
        
        $em->persist($report);
        $em->flush();
        
        return $this->json([
            'message' => '✅ Commentaire signalé avec succès. Le superviseur sera notifié.'
        ], 201);
    }
}
