<?php

namespace App\Controller;

use App\Repository\SupportMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @deprecated Cette classe est dupliquée. Utilisez App\Controller\Admin\AdminMessagesController
 * Ce fichier est conservé pour la compatibilité mais ne devrait plus être utilisé
 */
#[Route('/admin/messages-deprecated', name: 'admin_messages_deprecated_')]
#[IsGranted('ROLE_ADMIN')]
class AdminMessagesController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(SupportMessageRepository $messageRepository): Response
    {
        return $this->redirectToRoute('admin_messages_index');
    }
}
