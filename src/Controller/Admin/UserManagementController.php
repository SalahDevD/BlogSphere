<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $role = $request->query->get('role', '');

        if ($search) {
            $users = $userRepository->findBySearchTerm($search);
        } elseif ($role) {
            $users = $userRepository->findByRole($role);
        } else {
            $users = $userRepository->findAll();
        }

        $roles = ['ROLE_USER', 'ROLE_EDITOR', 'ROLE_SUPERVISOR', 'ROLE_ADMIN'];

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'search' => $search,
            'selectedRole' => $role,
            'roles' => $roles,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            // Verify CSRF token
            if (!$this->isCsrfTokenValid('edit' . $user->getId(), $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide');
                return $this->redirectToRoute('admin_users_index');
            }

            $newRoles = $request->request->all()['roles'] ?? [];
            if (empty($newRoles)) {
                $this->addFlash('error', 'L\'utilisateur doit avoir au moins un rôle');
                return $this->redirectToRoute('admin_users_edit', ['id' => $user->getId()]);
            }

            $user->setRoles($newRoles);
            $em->flush();

            $this->addFlash('success', 'Rôles de l\'utilisateur mis à jour.');
            return $this->redirectToRoute('admin_users_index');
        }

        $allRoles = ['ROLE_USER', 'ROLE_EDITOR', 'ROLE_SUPERVISOR', 'ROLE_ADMIN'];

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'allRoles' => $allRoles,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('admin_users_index');
        }

        // Empêcher la suppression du dernier admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $adminCount = count($em->getRepository(User::class)->findByRole('ROLE_ADMIN'));
            if ($adminCount === 1) {
                $this->addFlash('error', 'Impossible de supprimer le dernier administrateur.');
                return $this->redirectToRoute('admin_users_index');
            }
        }

        $userName = $user->getName();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur ' . $userName . ' supprimé avec succès.');
        return $this->redirectToRoute('admin_users_index');
    }
}
