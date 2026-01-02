<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'user_profile_')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    /**
     * 👤 Afficher le profil de l'utilisateur connecté
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        ArticleRepository $articleRepository,
        CommentRepository $commentRepository
    ): Response {
        $user = $this->getUser();
        
        // Articles de l'utilisateur
        $myArticles = $articleRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );
        
        // Commentaires récents
        $myComments = $commentRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC'],
            10
        );
        
        // Statistiques
        $stats = [
            'totalArticles' => count($myArticles),
            'publishedArticles' => $articleRepository->count([
                'author' => $user,
                'validationStatus' => 'approved'
            ]),
            'pendingArticles' => $articleRepository->count([
                'author' => $user,
                'validationStatus' => 'pending'
            ]),
            'rejectedArticles' => $articleRepository->count([
                'author' => $user,
                'validationStatus' => 'rejected'
            ]),
            'totalComments' => $commentRepository->count(['author' => $user])
        ];

        // Vérifier si l'utilisateur a au moins un article approuvé
        $hasApprovedArticle = $stats['publishedArticles'] > 0;
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'articles' => $myArticles,
            'comments' => $myComments,
            'stats' => $stats,
            'hasApprovedArticle' => $hasApprovedArticle
        ]);
    }
    
    /**
     * ✏️ Modifier le profil
     */
    #[Route('/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $bio = $request->request->get('bio');
            
            if ($name && $name !== $user->getName()) {
                $user->setName($name);
            }
            
            if ($bio !== null) {
                $user->setBio($bio);
            }
            
            $em->flush();
            
            $this->addFlash('success', '✅ Profil mis à jour avec succès !');
            return $this->redirectToRoute('user_profile_index');
        }
        
        return $this->render('profile/edit.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * 🔒 Changer le mot de passe
     */
    #[Route('/change-password', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        
        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');
            
            // Vérifier le mot de passe actuel
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', '❌ Mot de passe actuel incorrect');
                return $this->redirectToRoute('user_profile_change_password');
            }
            
            // Vérifier la correspondance
            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', '❌ Les mots de passe ne correspondent pas');
                return $this->redirectToRoute('user_profile_change_password');
            }
            
            // Vérifier la longueur
            if (strlen($newPassword) < 6) {
                $this->addFlash('error', '❌ Le mot de passe doit contenir au moins 6 caractères');
                return $this->redirectToRoute('user_profile_change_password');
            }
            
            // Mettre à jour
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $em->flush();
            
            $this->addFlash('success', '✅ Mot de passe modifié avec succès !');
            return $this->redirectToRoute('user_profile_index');
        }
        
        return $this->render('profile/change_password.html.twig');
    }
    
    /**
     * 📝 Liste des articles de l'utilisateur
     */
    #[Route('/articles', name: 'articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();
        $articles = $articleRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );
        
        return $this->render('profile/articles.html.twig', [
            'articles' => $articles
        ]);
    }
}
