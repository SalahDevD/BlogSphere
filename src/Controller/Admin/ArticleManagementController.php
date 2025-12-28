<?php
namespace App\Controller\Admin;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/articles')]
#[IsGranted('ROLE_ADMIN')]
class ArticleManagementController extends AbstractController
{
    #[Route('/', name: 'admin_article_index')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();
        
        return $this->render('admin/articles/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'admin_article_toggle_status')]
    public function toggleStatus(int $id, ArticleRepository $articleRepository, EntityManagerInterface $em): Response
    {
        $article = $articleRepository->find($id);
        
        if ($article->getStatus() === 'published') {
            $article->setStatus('draft');
        } else {
            $article->setStatus('published');
            if (!$article->getPublishedAt()) {
                $article->setPublishedAt(new \DateTime());
            }
        }
        
        $em->flush();
        $this->addFlash('success', 'Statut de l\'article modifié avec succès !');
        
        return $this->redirectToRoute('admin_article_index');
    }
}
