<?php

namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/stats', name: 'admin_stats_')]
#[IsGranted('ROLE_ADMIN')]
class StatsController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        ArticleRepository $articleRepository,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        // Articles par jour (derniers 30 jours)
        $articlesPerDay = $this->getArticlesPerDay($articleRepository);
        
        // Données globales
        $totalArticles = count($articleRepository->findAll());
        $publishedArticles = count($articleRepository->findBy(['validationStatus' => 'approved']));
        $pendingArticles = count($articleRepository->findBy(['validationStatus' => 'pending']));
        $draftArticles = count($articleRepository->findBy(['validationStatus' => 'draft']));
        $rejectedArticles = count($articleRepository->findBy(['validationStatus' => 'rejected']));
        
        // Commentaires
        $totalComments = count($commentRepository->findAll());
        $approvedComments = count($commentRepository->findBy(['status' => 'approved']));
        $pendingComments = count($commentRepository->findBy(['status' => 'pending']));
        
        // Utilisateurs
        $totalUsers = count($userRepository->findAll());
        $adminUsers = count($userRepository->findByRole('ROLE_ADMIN'));
        $editorUsers = count($userRepository->findByRole('ROLE_EDITOR'));
        $supervisorUsers = count($userRepository->findByRole('ROLE_SUPERVISOR'));
        
        // Articles par catégorie
        $articlesByCategory = $this->getArticlesByCategory($articleRepository);
        
        // Articles par auteur (top 5)
        $topAuthors = $this->getTopAuthors($articleRepository);
        
        // Réactions sur les articles
        $reactionStats = $this->getReactionStats($em);

        return $this->render('admin/stats/dashboard.html.twig', [
            'articlesPerDay' => $articlesPerDay,
            'totalArticles' => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'pendingArticles' => $pendingArticles,
            'draftArticles' => $draftArticles,
            'rejectedArticles' => $rejectedArticles,
            'totalComments' => $totalComments,
            'approvedComments' => $approvedComments,
            'pendingComments' => $pendingComments,
            'totalUsers' => $totalUsers,
            'adminUsers' => $adminUsers,
            'editorUsers' => $editorUsers,
            'supervisorUsers' => $supervisorUsers,
            'articlesByCategory' => $articlesByCategory,
            'topAuthors' => $topAuthors,
            'reactionStats' => $reactionStats,
        ]);
    }

    #[Route('', name: 'index')]
    public function index(
        ArticleRepository $articleRepository,
        CommentRepository $commentRepository,
        UserRepository $userRepository
    ): Response {
        // Stats générales
        $totalArticles = count($articleRepository->findAll());
        $publishedArticles = count($articleRepository->findBy(['validationStatus' => 'approved']));
        $pendingArticles = count($articleRepository->findBy(['validationStatus' => 'pending']));
        $totalComments = count($commentRepository->findAll());
        $totalUsers = count($userRepository->findAll());

        // Récupérer les articles par jour pour le graphique
        $articlesPerDay = $this->getArticlesPerDay($articleRepository);
        
        // Récupérer les articles par catégorie
        $articlesByCategory = $this->getArticlesByCategory($articleRepository);

        // Articles les plus appréciés
        $mostLikedArticles = $articleRepository->findMostLiked(5);

        return $this->render('admin/stats/index.html.twig', [
            'totalArticles' => $totalArticles,
            'publishedArticles' => $publishedArticles,
            'pendingArticles' => $pendingArticles,
            'totalComments' => $totalComments,
            'totalUsers' => $totalUsers,
            'articlesPerDay' => $articlesPerDay,
            'articlesByCategory' => $articlesByCategory,
            'mostLikedArticles' => $mostLikedArticles,
        ]);
    }

    private function getArticlesPerDay(ArticleRepository $repo): array
    {
        $articles = $repo->findAll();
        $dataByDay = [];

        foreach ($articles as $article) {
            $day = $article->getCreatedAt()->format('Y-m-d');
            if (!isset($dataByDay[$day])) {
                $dataByDay[$day] = 0;
            }
            $dataByDay[$day]++;
        }

        ksort($dataByDay);
        return array_slice($dataByDay, -30);
    }

    private function getArticlesByCategory(ArticleRepository $repo): array
    {
        $articles = $repo->findAll();
        $dataByCategory = [];

        foreach ($articles as $article) {
            if ($article->getCategory()) {
                $category = $article->getCategory()->getName();
                if (!isset($dataByCategory[$category])) {
                    $dataByCategory[$category] = 0;
                }
                $dataByCategory[$category]++;
            }
        }

        arsort($dataByCategory);
        return $dataByCategory;
    }

    private function getTopAuthors(ArticleRepository $repo): array
    {
        $articles = $repo->findBy(['validationStatus' => 'approved']);
        $authorCounts = [];

        foreach ($articles as $article) {
            $authorName = $article->getAuthor()->getName();
            if (!isset($authorCounts[$authorName])) {
                $authorCounts[$authorName] = 0;
            }
            $authorCounts[$authorName]++;
        }

        arsort($authorCounts);
        return array_slice($authorCounts, 0, 5);
    }

    private function getReactionStats(EntityManagerInterface $em): array
    {
        $connection = $em->getConnection();
        $result = $connection->executeQuery(
            'SELECT SUM(CASE WHEN is_like = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN is_like = 0 THEN 1 ELSE 0 END) as dislikes
             FROM reactions'
        );
        
        $data = $result->fetchAssociative();
        return [
            'likes' => $data['likes'] ?? 0,
            'dislikes' => $data['dislikes'] ?? 0,
        ];
    }
}
