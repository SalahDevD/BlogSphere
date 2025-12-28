<?php

namespace App\Controller;

use App\Entity\SupportMessage;
use App\Entity\User;
use App\Repository\SupportMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/support', name: 'api_support')]
#[IsGranted('ROLE_USER')]
class SupportController extends AbstractController
{
    #[Route('/messages', name: 'support_messages_list', methods: ['GET'])]
    public function listMessages(SupportMessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        
        // Les utilisateurs voient uniquement leurs messages
        // Les superviseurs voient tous les messages
        if ($this->isGranted('ROLE_SUPERVISOR')) {
            $messages = $messageRepository->findBy([], ['createdAt' => 'DESC']);
        } else {
            $messages = $messageRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        }
        
        $data = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'subject' => $message->getSubject(),
                'message' => $message->getMessage(),
                'status' => $message->getStatus(),
                'response' => $message->getResponse(),
                'userId' => $message->getUser()->getId(),
                'userName' => $message->getUser()->getUsername(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'respondedAt' => $message->getRespondedAt()?->format('Y-m-d H:i:s')
            ];
        }, $messages);
        
        return $this->json($data);
    }
    
    #[Route('/message/send', name: 'support_send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['subject']) || !isset($data['message'])) {
            return $this->json(['error' => 'Sujet et message requis'], 400);
        }
        
        $supportMessage = new SupportMessage();
        $supportMessage->setUser($user);
        $supportMessage->setSubject($data['subject']);
        $supportMessage->setMessage($data['message']);
        $supportMessage->setStatus('PENDING');
        $supportMessage->setCreatedAt(new \DateTime());
        
        $em->persist($supportMessage);
        $em->flush();
        
        return $this->json([
            'message' => 'Message envoyé au support',
            'id' => $supportMessage->getId()
        ], 201);
    }
    
    #[Route('/message/{id}', name: 'support_view_message', methods: ['GET'])]
    public function viewMessage(
        int $id,
        SupportMessageRepository $messageRepository
    ): Response {
        $user = $this->getUser();
        $message = $messageRepository->find($id);
        
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }
        
        // Vérifier les permissions
        if ($message->getUser() !== $user && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }
        
        return $this->json([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'status' => $message->getStatus(),
            'response' => $message->getResponse(),
            'userName' => $message->getUser()->getUsername(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'respondedAt' => $message->getRespondedAt()?->format('Y-m-d H:i:s')
        ]);
    }
}
