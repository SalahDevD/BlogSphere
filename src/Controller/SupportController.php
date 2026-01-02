<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Repository\ChatMessageRepository;
use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

#[Route('/api/chat', name: 'api_chat')]
class SupportController extends AbstractController
{
    #[Route('/test', name: 'test_chat', methods: ['GET'])]
    public function testChat(): JsonResponse
    {
        try {
            $service = new ChatbotService();
            $response = $service->generateResponse('test');
            return new JsonResponse([
                'status' => 'ok',
                'message' => $response
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/message/send', name: 'send_message', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em,
        ChatbotService $chatbotService,
        ChatMessageRepository $chatRepository
    ): JsonResponse {
        try {
            // Récupérer l'utilisateur
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(['error' => 'Utilisateur non authentifié'], 401);
            }
            
            // Récupérer le contenu du message
            $userMessage = null;
            $contentType = $request->headers->get('Content-Type', '');
            
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($request->getContent(), true);
                $userMessage = $data['content'] ?? null;
            } else {
                $userMessage = $request->request->get('content');
            }

            if (!$userMessage || empty(trim($userMessage))) {
                return new JsonResponse(['error' => 'Le contenu du message est requis'], 400);
            }

            // Générer la réponse du chatbot
            $botResponse = 'Désolé, une erreur s\'est produite.';
            try {
                $botResponse = $chatbotService->generateResponse($userMessage);
            } catch (Throwable $e) {
                error_log('ChatbotService Error: ' . $e->getMessage());
            }

            // Enregistrer la conversation
            try {
                $chatMsg = new ChatMessage();
                $chatMsg->setUser($user);
                $chatMsg->setUserMessage($userMessage);
                $chatMsg->setBotResponse($botResponse);

                $em->persist($chatMsg);
                $em->flush();

                return new JsonResponse([
                    'success' => true,
                    'userMessage' => $userMessage,
                    'botResponse' => $botResponse,
                    'id' => $chatMsg->getId()
                ], 201);
            } catch (Throwable $e) {
                error_log('Database Error: ' . $e->getMessage());
                // Retourner le message du chatbot même si la sauvegarde échoue
                return new JsonResponse([
                    'success' => true,
                    'userMessage' => $userMessage,
                    'botResponse' => $botResponse
                ], 200);
            }
        } catch (Throwable $e) {
            error_log('ChatBot Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'error' => 'Erreur serveur',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    #[Route('/messages', name: 'get_messages', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMessages(ChatMessageRepository $chatRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer toutes les conversations du chatbot
        $messages = $chatRepository->findByUser($user);

        $data = [];
        foreach ($messages as $message) {
            // Message de l'utilisateur
            $data[] = [
                'id' => $message->getId() . '_user',
                'content' => $message->getUserMessage(),
                'isFromUser' => true,
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'createdAtTime' => $message->getCreatedAt()->format('H:i'),
            ];
            
            // Réponse du chatbot
            $data[] = [
                'id' => $message->getId() . '_bot',
                'content' => $message->getBotResponse(),
                'isFromUser' => false,
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'createdAtTime' => $message->getCreatedAt()->format('H:i'),
            ];
        }

        return $this->json($data);
    }
}

