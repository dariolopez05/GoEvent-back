<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ChatController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $openAiApiKey;

    public function __construct(HttpClientInterface $httpClient, string $openAiApiKey)
    {
        $this->httpClient = $httpClient;
        $this->openAiApiKey = $openAiApiKey;
    }

    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';

        if (empty($userMessage)) {
            return new JsonResponse(['error' => 'Missing message'], 400);
        }

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey, 
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', 
                    'messages' => [
                        ['role' => 'user', 'content' => $userMessage]
                    ]
                ]
            ]);

            $responseData = $response->toArray(false);

            file_put_contents('/tmp/openai_response.json', json_encode($responseData, JSON_PRETTY_PRINT));

            if (isset($responseData['choices'][0]['message']['content'])) {
                $reply = $responseData['choices'][0]['message']['content'];
            } else {
                $reply = 'No response from OpenAI API';
            }

            return new JsonResponse(['reply' => $reply]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'API call failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}