<?php

namespace App\Controller;

use App\Entity\SupportMessage;
use App\Repository\SupportMessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/contact-support')]
#[IsGranted('ROLE_USER')]
class ContactController extends AbstractController
{
    #[Route('', name: 'app_contact_support')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        SupportMessageRepository $supportMessageRepository
    ): Response {
        $user = $this->getUser();
        
        // Check if user is admin or supervisor
        $isAdminOrSupervisor = in_array('ROLE_ADMIN', $user->getRoles()) || 
                              in_array('ROLE_SUPERVISOR', $user->getRoles());
        
        // For regular users, show their conversations and allow them to create messages
        if (!$isAdminOrSupervisor) {
            $conversations = $supportMessageRepository->findConversationsByUser($user);

            if ($request->isMethod('POST')) {
                $subject = $request->request->get('subject');
                $message = $request->request->get('message');
                $messageType = $request->request->get('type', 'question');

                if (!$subject || !$message) {
                    $this->addFlash('error', 'Tous les champs sont requis.');
                    return $this->redirectToRoute('app_contact_support');
                }

                try {
                    $supportMsg = new SupportMessage();
                    $supportMsg->setSender($user);
                    $supportMsg->setContent($message);
                    $supportMsg->setSubject($subject);
                    $supportMsg->setMessageType($messageType);
                    
                    $em->persist($supportMsg);
                    $em->flush();

                    $this->addFlash('success', 'Votre message a été envoyé avec succès. Un superviseur ou administrateur vous répondra bientôt.');
                    return $this->redirectToRoute('app_contact_support');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
                }
            }

            return $this->render('contact/support.html.twig', [
                'conversations' => $conversations,
                'isStaffMember' => false
            ]);
        }
        
        // For admins/supervisors, show all support conversations
        $conversations = $supportMessageRepository->findAll();
        
        return $this->render('contact/support.html.twig', [
            'conversations' => $conversations,
            'isStaffMember' => true
        ]);
    }

    #[Route('/conversation/{id}', name: 'app_contact_support_conversation')]
    public function conversation(
        SupportMessage $message,
        SupportMessageRepository $supportMessageRepository
    ): Response {
        $user = $this->getUser();

        // Check if user is the sender or a supervisor/admin
        if ($message->getSender()->getId() !== $user->getId() && 
            !in_array('ROLE_ADMIN', $user->getRoles()) && 
            !in_array('ROLE_SUPERVISOR', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette conversation.');
        }

        // Get all messages in this conversation
        $conversationMessages = $supportMessageRepository->findConversationThread($message);

        // Mark as read if user is admin/supervisor
        if (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_SUPERVISOR', $user->getRoles())) {
            // Implementation for marking as read can be added here
        }

        return $this->render('contact/conversation.html.twig', [
            'mainMessage' => $message,
            'conversationMessages' => $conversationMessages
        ]);
    }

    #[Route('/reply/{id}', name: 'app_contact_support_reply', methods: ['POST'])]
    public function reply(
        SupportMessage $message,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        // Check if user is admin or supervisor (can reply to support)
        $isAdminOrSupervisor = in_array('ROLE_ADMIN', $user->getRoles()) || 
                              in_array('ROLE_SUPERVISOR', $user->getRoles());
        
        if (!$isAdminOrSupervisor) {
            throw $this->createAccessDeniedException('Seuls les administrateurs et superviseurs peuvent répondre.');
        }

        $replyContent = $request->request->get('reply');

        if (!$replyContent) {
            $this->addFlash('error', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('app_contact_support_conversation', ['id' => $message->getId()]);
        }

        try {
            $reply = new SupportMessage();
            $reply->setSender($user);
            $reply->setReceiver($message->getSender());
            $reply->setContent($replyContent);
            $reply->setParentMessage($message);
            
            $em->persist($reply);
            $em->flush();

            $this->addFlash('success', 'Votre réponse a été envoyée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de la réponse.');
        }

        return $this->redirectToRoute('app_contact_support_conversation', ['id' => $message->getId()]);
    }
}
