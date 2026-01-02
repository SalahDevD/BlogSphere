<?php

namespace App\Controller\Admin;

use App\Entity\SupportMessage;
use App\Repository\SupportMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/messages', name: 'admin_messages_')]
class AdminMessagesController extends AbstractController
{
    /**
     * 📋 Liste de tous les messages des clients
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SupportMessageRepository $messageRepository): Response
    {
        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Accès refusé. Vous devez être administrateur ou superviseur.');
        }

        // Récupérer tous les messages triés par date décroissante
        $messages = $messageRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        // Grouper les messages par conversation (client)
        $conversations = [];
        foreach ($messages as $message) {
            $senderId = $message->getSender()->getId();
            if (!isset($conversations[$senderId])) {
                $conversations[$senderId] = [
                    'sender' => $message->getSender(),
                    'lastMessage' => $message,
                    'messages' => [],
                    'unreadCount' => 0
                ];
            }
            $conversations[$senderId]['messages'][] = $message;
            if (!$message->isRead()) {
                $conversations[$senderId]['unreadCount']++;
            }
        }

        return $this->render('admin/messages/index.html.twig', [
            'conversations' => $conversations,
            'messages' => $messages
        ]);
    }

    /**
     * 💬 Voir la conversation complète avec un client
     */
    #[Route('/client/{clientId}', name: 'client_conversation', methods: ['GET'])]
    public function clientConversation(
        int $clientId,
        SupportMessageRepository $messageRepository,
        EntityManagerInterface $em
    ): Response {
        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPERVISOR')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Récupérer tous les messages avec ce client
        $messages = $messageRepository->findBy(
            ['sender' => $clientId],
            ['createdAt' => 'ASC']
        );

        if (empty($messages)) {
            throw $this->createNotFoundException('Aucun message trouvé pour ce client');
        }

        // Marquer tous les messages comme lus
        foreach ($messages as $message) {
            $message->setIsRead(true);
        }
        $em->flush();

        $client = $messages[0]->getSender();

        return $this->render('admin/messages/conversation.html.twig', [
            'client' => $client,
            'messages' => $messages
        ]);
    }

    /**
     * 📨 Envoyer une réponse à un client
     */
    #[Route('/reply/{clientId}', name: 'send_reply', methods: ['POST'])]
    public function sendReply(
        int $clientId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content']) || empty($data['content'])) {
            return $this->json(['error' => 'Le contenu du message est requis'], 400);
        }

        $admin = $this->getUser();
        $client = $em->getRepository(\App\Entity\User::class)->find($clientId);

        if (!$client) {
            return $this->json(['error' => 'Client non trouvé'], 404);
        }

        // Créer un message de réponse
        $reply = new SupportMessage();
        $reply->setSender($admin);
        $reply->setReceiver($client);
        $reply->setContent($data['content']);
        $reply->setIsRead(true);
        $reply->setCreatedAt(new \DateTime());

        $em->persist($reply);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Réponse envoyée avec succès',
            'id' => $reply->getId(),
            'senderName' => $admin->getName(),
            'content' => $reply->getContent(),
            'createdAt' => $reply->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }

    /**
     * 📨 API pour récupérer les messages d'une conversation
     */
    #[Route('/api/messages/{clientId}', name: 'api_get_messages', methods: ['GET'])]
    public function getClientMessages(
        int $clientId,
        SupportMessageRepository $messageRepository
    ): Response {
        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $messages = $messageRepository->findBy(
            ['sender' => $clientId],
            ['createdAt' => 'ASC']
        );

        $data = array_map(function(SupportMessage $message) {
            return [
                'id' => $message->getId(),
                'senderName' => $message->getSender()->getName(),
                'senderEmail' => $message->getSender()->getEmail(),
                'content' => $message->getContent(),
                'isRead' => $message->isRead(),
                'isFromAdmin' => $message->getReceiver() !== null,
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'createdAtTime' => $message->getCreatedAt()->format('H:i')
            ];
        }, $messages);

        return $this->json($data);
    }

    /**
     * ✓ Marquer un message comme lu
     */
    #[Route('/mark-read/{messageId}', name: 'mark_read', methods: ['POST'])]
    public function markAsRead(
        int $messageId,
        SupportMessageRepository $messageRepository,
        EntityManagerInterface $em
    ): Response {
        // Vérifier les permissions
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_SUPERVISOR')) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        $message = $messageRepository->find($messageId);

        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], 404);
        }

        $message->setIsRead(true);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
