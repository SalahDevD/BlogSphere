<?php

namespace App\Controller\Supervisor;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/supervisor/moderation', name: 'supervisor_moderation_')]
#[IsGranted('ROLE_SUPERVISOR')]
class ModerationController extends AbstractController
{
    /**
     * 📋 Dashboard superviseur - Vue d'ensemble
     */
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        ArticleRepository $articleRepository,
        ReportRepository $reportRepository
    ): Response {
        // Articles en attente de validation
        $pendingArticles = $articleRepository->findBy(
            ['validationStatus' => 'pending'],
            ['createdAt' => 'DESC']
        );
        
        // Signalements en attente
        $pendingReports = $reportRepository->findBy(
            ['status' => 'PENDING'],
            ['createdAt' => 'DESC']
        );
        
        return $this->render('supervisor/moderation/dashboard.html.twig', [
            'pendingArticles' => $pendingArticles,
            'pendingReports' => $pendingReports,
        ]);
    }
    
    /**
     * 📝 Liste des articles en attente de validation (API JSON)
     */
    #[Route('/articles/pending', name: 'pending_articles', methods: ['GET'])]
    public function listPendingArticles(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(
            ['validationStatus' => 'pending'],
            ['createdAt' => 'DESC']
        );
        
        $data = array_map(function($article) {
            return [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'author' => $article->getAuthor()->getName(),
                'authorEmail' => $article->getAuthor()->getEmail(),
                'authorId' => $article->getAuthor()->getId(),
                'excerpt' => substr($article->getContent(), 0, 150) . '...',
                'createdAt' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
                'category' => $article->getCategory()?->getName()
            ];
        }, $articles);
        
        return $this->json($data);
    }
    
    /**
     * ✅ Valider un article (le rendre public)
     */
    #[Route('/article/{id}/validate', name: 'validate_article', methods: ['POST'])]
    public function validateArticle(
        Article $article,
        EntityManagerInterface $em
    ): Response {
        if ($article->getValidationStatus() !== 'pending') {
            $this->addFlash('danger', '❌ Cet article n\'est pas en attente de validation.');
            return $this->redirectToRoute('supervisor_moderation_dashboard');
        }
        
        // Approuver l'article
        $article->setValidationStatus('approved');
        $article->setPublishedAt(new \DateTime());
        
        $em->flush();
        
        $this->addFlash('success', '✅ Article validé et publié avec succès.');
        
        return $this->redirectToRoute('supervisor_moderation_dashboard');
    }
    
    /**
     * ❌ Rejeter un article
     */
    #[Route('/article/{id}/reject', name: 'reject_article', methods: ['POST'])]
    public function rejectArticle(
        Article $article,
        EntityManagerInterface $em
    ): Response {
        if ($article->getValidationStatus() !== 'pending') {
            $this->addFlash('danger', '❌ Cet article n\'est pas en attente de validation.');
            return $this->redirectToRoute('supervisor_moderation_dashboard');
        }
        
        $article->setValidationStatus('rejected');
        $em->flush();
        
        $this->addFlash('warning', '❌ Article rejeté.');
        
        return $this->redirectToRoute('supervisor_moderation_dashboard');
    }
    
    /**
     * 🚩 Liste de tous les signalements (API JSON)
     */
    #[Route('/reports', name: 'reports', methods: ['GET'])]
    public function listReports(ReportRepository $reportRepository): Response
    {
        $reports = $reportRepository->findBy(
            ['status' => 'PENDING'],
            ['createdAt' => 'DESC']
        );
        
        $data = array_map(function($report) {
            return [
                'id' => $report->getId(),
                'reason' => $report->getReason(),
                'status' => $report->getStatus(),
                'reportedBy' => $report->getReporter()->getName(),
                'reporterEmail' => $report->getReporter()->getEmail(),
                'type' => $report->getArticle() ? 'article' : 'comment',
                'articleId' => $report->getArticle()?->getId(),
                'articleTitle' => $report->getArticle()?->getTitle(),
                'commentId' => $report->getComment()?->getId(),
                'commentContent' => $report->getComment() ? substr($report->getComment()->getContent(), 0, 100) . '...' : null,
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $reports);
        
        return $this->json($data);
    }
    
    /**
     * ✅ Approuver un signalement et supprimer le contenu
     */
    #[Route('/report/{id}/approve', name: 'approve_report', methods: ['POST'])]
    public function approveReport(
        int $id,
        ReportRepository $reportRepository,
        EntityManagerInterface $em
    ): Response {
        $report = $reportRepository->find($id);
        
        if (!$report) {
            $this->addFlash('danger', '❌ Signalement non trouvé.');
            return $this->redirectToRoute('supervisor_moderation_dashboard');
        }
        
        // Supprimer l'article ou commentaire signalé
        if ($article = $report->getArticle()) {
            $em->remove($article);
            $message = '✅ Article supprimé avec succès.';
        } elseif ($comment = $report->getComment()) {
            $em->remove($comment);
            $message = '✅ Commentaire supprimé avec succès.';
        }
        
        $report->setStatus('APPROVED');
        $em->flush();
        
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('supervisor_moderation_dashboard');
    }
    
    /**
     * ❌ Rejeter un signalement (garder le contenu)
     */
    #[Route('/report/{id}/reject', name: 'reject_report', methods: ['POST'])]
    public function rejectReport(
        int $id,
        ReportRepository $reportRepository,
        EntityManagerInterface $em
    ): Response {
        $report = $reportRepository->find($id);
        
        if (!$report) {
            $this->addFlash('danger', '❌ Signalement non trouvé.');
            return $this->redirectToRoute('supervisor_moderation_dashboard');
        }
        
        $report->setStatus('REJECTED');
        $em->flush();
        
        $this->addFlash('info', 'ℹ️ Signalement rejeté. Le contenu est conservé.');
        
        return $this->redirectToRoute('supervisor_moderation_dashboard');
    }
    
    /**
     * 💬 Liste des commentaires signalés (API JSON)
     */
    #[Route('/comments/reported', name: 'reported_comments', methods: ['GET'])]
    public function listReportedComments(ReportRepository $reportRepository): Response
    {
        $reports = $reportRepository->createQueryBuilder('r')
            ->where('r.comment IS NOT NULL')
            ->andWhere('r.status = :status')
            ->setParameter('status', 'PENDING')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        $data = array_map(function($report) {
            $comment = $report->getComment();
            return [
                'reportId' => $report->getId(),
                'commentId' => $comment->getId(),
                'commentContent' => $comment->getContent(),
                'commentAuthor' => $comment->getAuthor()->getName(),
                'reportReason' => $report->getReason(),
                'reportedBy' => $report->getReporter()->getName(),
                'articleTitle' => $comment->getArticle()->getTitle(),
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $reports);
        
        return $this->json($data);
    }
}
