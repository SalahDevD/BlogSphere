<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        ArticleRepository $articleRepository,
        UserRepository $userRepository,
        ReportRepository $reportRepository
        // ajoute CommentRepository si tu veux un vrai count
    ): Response {
        // Articles
        $totalArticles     = $articleRepository->count([]);
        $publishedArticles = $articleRepository->count(['validationStatus' => 'approved']);
        $pendingArticles   = $articleRepository->count(['validationStatus' => 'pending']);
        $rejectedArticles  = $articleRepository->count(['validationStatus' => 'rejected']);
        $draftArticles     = $articleRepository->count(['validationStatus' => 'draft']);

        // Utilisateurs
        $totalUsers    = $userRepository->count([]);
        $activeUsers   = $userRepository->count(['isActive' => true]);
        $disabledUsers = $userRepository->count(['isActive' => false]);

        // Signalements
        $pendingReports = $reportRepository->count(['status' => 'PENDING']);

        // Commentaires en attente (placeholder pour éviter l'erreur Twig)
        $pendingComments = 0; // tu remplaceras par un vrai count plus tard

        return $this->render('admin/dashboard.html.twig', [
            'totalArticles'     => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'pendingArticles'   => $pendingArticles,
            'rejectedArticles'  => $rejectedArticles,
            'draftArticles'     => $draftArticles,
            'totalUsers'        => $totalUsers,
            'activeUsers'       => $activeUsers,
            'disabledUsers'     => $disabledUsers,
            'pendingReports'    => $pendingReports,
            'pendingComments'   => $pendingComments,
        ]);
    }
}
