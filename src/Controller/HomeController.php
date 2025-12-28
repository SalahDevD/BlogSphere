<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // 🎯 Articles les plus aimés pour le carousel (uniquement validés)
        $popularArticles = $articleRepository->findPopularArticles(5);
        
        // 📰 Derniers articles approuvés
        $latestArticles = $articleRepository->findBy(
            ['validationStatus' => 'approved'],
            ['createdAt' => 'DESC'],
            10
        );
        
        return $this->render('home/index.html.twig', [
            'popularArticles' => $popularArticles,
            'latestArticles' => $latestArticles,
        ]);
    }
}
