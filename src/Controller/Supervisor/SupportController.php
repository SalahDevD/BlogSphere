<?php

namespace App\Controller\Supervisor;

use App\Entity\SupportMessage;
use App\Repository\SupportMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/supervisor/support', name: 'api_supervisor_support')]
#[IsGranted('ROLE_SUPERVISOR')]
class SupportController extends AbstractController
{
    #[Route('/messages/pending', name: 'supervisor_support_pending', methods: ['GET'])]
    public function listPendingMessages(SupportMessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(['status' => 'PENDING'], ['createdAt' => 'ASC']);
        
        $data = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'subject' => $message->getSubject(),
                'message' => $message->getMessage(),
                'status' => $message->getStatus(),
                'userId' => $message->getUser()->getId(),
                'userName' => $message->getUser()->getUsername(),
                'userEmail' => $message->getUser()->getEmail(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $messages);
        
        return $this->json($data);
    }
    
    #[Route('/message/{id}/respond', name: 'supervisor_support_respond', methods: ['POST'])]
    public function respondToMessage(
        int $id,
        Request $request,
        SupportMessageRepository $messageRepository,
        EntityManagerInterface $em
    ): Response {
        $message = $messageRepository->find($id);
        
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['response'])) {
            return $this->json(['error' => 'Réponse requise'], 400);
        }
        
        $message->setResponse($data['response']);
        $message->setStatus('RESOLVED');
        $message->setRespondedAt(new \DateTime());
        $message->setRespondedBy($this->getUser());
        
        $em->flush();
        
        return $this->json([
            'message' => 'Réponse envoyée avec succès',
            'id' => $message->getId()
        ]);
    }
    
    #[Route('/messages/resolved', name: 'supervisor_support_resolved', methods: ['GET'])]
    public function listResolvedMessages(SupportMessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(['status' => 'RESOLVED'], ['respondedAt' => 'DESC'], 50);
        
        $data = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'subject' => $message->getSubject(),
                'message' => $message->getMessage(),
                'response' => $message->getResponse(),
                'status' => $message->getStatus(),
                'userName' => $message->getUser()->getUsername(),
                'respondedBy' => $message->getRespondedBy()?->getUsername(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'respondedAt' => $message->getRespondedAt()?->format('Y-m-d H:i:s')
            ];
        }, $messages);
        
        return $this->json($data);
    }
    
    #[Route('/message/{id}/close', name: 'supervisor_support_close', methods: ['POST'])]
    public function closeMessage(
        int $id,
        SupportMessageRepository $messageRepository,
        EntityManagerInterface $em
    ): Response {
        $message = $messageRepository->find($id);
        
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }
        
        $message->setStatus('CLOSED');
        $em->flush();
        
        return $this->json(['message' => 'Ticket fermé']);
    }
}
