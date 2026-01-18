<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(
        Request $request,
        ArticleRepository $articleRepository,
        UserRepository $userRepository
    ): Response {
        $query = $request->query->get('q', '');
        $results = [];

        if (!empty($query)) {
            // Search articles
            $articles = $articleRepository->createQueryBuilder('a')
                ->where('a.title LIKE :search OR a.content LIKE :search')
                ->setParameter('search', '%' . $query . '%')
                ->getQuery()
                ->getResult();

            // Search users
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $query . '%')
                ->getQuery()
                ->getResult();

            $results = [
                'articles' => $articles,
                'users' => $users,
                'query' => $query,
            ];
        }

        return $this->render('admin/search_results.html.twig', $results);
    }
}
