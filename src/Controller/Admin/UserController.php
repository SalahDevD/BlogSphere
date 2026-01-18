<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin_users_index')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('search', '');
        $selectedRole = $request->query->get('role', '');

        $users = $userRepository->findAll();

        // Apply search filter
        if ($search) {
            $users = array_filter($users, function($user) use ($search) {
                $searchLower = strtolower($search);
                return strpos(strtolower($user->getName()), $searchLower) !== false ||
                       strpos(strtolower($user->getEmail()), $searchLower) !== false;
            });
            $users = array_values($users);
        }

        // Apply role filter
        if ($selectedRole) {
            $users = array_filter($users, function($user) use ($selectedRole) {
                return in_array($selectedRole, $user->getRoles());
            });
            $users = array_values($users);
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'search' => $search,
            'selectedRole' => $selectedRole,
        ]);
    }

    #[Route('/filter', name: 'admin_users_filter', methods: ['GET'])]
    public function filter(Request $request, UserRepository $userRepository): JsonResponse
    {
        $search = $request->query->get('search', '');
        $selectedRole = $request->query->get('role', '');

        $users = $userRepository->findAll();

        // Apply search filter
        if ($search) {
            $users = array_filter($users, function($user) use ($search) {
                $searchLower = strtolower($search);
                return strpos(strtolower($user->getName()), $searchLower) !== false ||
                       strpos(strtolower($user->getEmail()), $searchLower) !== false;
            });
            $users = array_values($users);
        }

        // Apply role filter
        if ($selectedRole) {
            $users = array_filter($users, function($user) use ($selectedRole) {
                return in_array($selectedRole, $user->getRoles());
            });
            $users = array_values($users);
        }

        try {
            $html = $this->renderView('admin/users/table.html.twig', [
                'users' => $users,
            ]);

            return new JsonResponse([
                'success' => true,
                'html' => $html,
                'count' => count($users),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/new', name: 'admin_users_new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure firstName and lastName are set
            if (!$user->getFirstName()) {
                $user->setFirstName($user->getName());
            }
            if (!$user->getLastName()) {
                $user->setLastName($user->getName());
            }
            
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès !');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ensure firstName and lastName are set
            if (!$user->getFirstName()) {
                $user->setFirstName($user->getName());
            }
            if (!$user->getLastName()) {
                $user->setLastName($user->getName());
            }
            
            // Hash password if changed
            if ($form->get('password')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
                $user->setPassword($hashedPassword);
            }
            
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié avec succès !');
            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_users_toggle', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsActive(!$user->isActive());
        $em->flush();

        $status = $user->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Utilisateur $status avec succès !");
        
        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/delete', name: 'admin_users_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        // Handle GET request - show confirmation page
        if ($request->getMethod() === 'GET') {
            return $this->render('admin/users/confirm_delete.html.twig', [
                'user' => $user,
            ]);
        }

        // Handle POST request - delete the user
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_users_index');
    }
}
