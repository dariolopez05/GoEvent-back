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

    // Constructor que inyecta el cliente HTTP y la API key
    public function __construct(HttpClientInterface $httpClient, string $openAiApiKey)
    {
        $this->httpClient = $httpClient;
        $this->openAiApiKey = $openAiApiKey;
    }

    #[Route('/api/chat', name: 'api_chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        // Decodifica los datos de la solicitud
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? ''; // Aseguramos que se obtiene el mensaje del usuario

        // Verifica si el mensaje no está vacío
        if (empty($userMessage)) {
            return new JsonResponse(['error' => 'Missing message'], 400);
        }

        try {
            // Realiza la solicitud a la API de OpenAI
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openAiApiKey, // Usa la API key inyectada correctamente
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Usando el modelo adecuado
                    'messages' => [
                        ['role' => 'user', 'content' => $userMessage] // Envía el mensaje del usuario
                    ]
                ]
            ]);

            // Convierte la respuesta a un array y maneja posibles fallos
            $responseData = $response->toArray(false);

            // Registra la respuesta para depuración
            file_put_contents('/tmp/openai_response.json', json_encode($responseData, JSON_PRETTY_PRINT));

            // Verifica si la respuesta tiene la estructura esperada
            if (isset($responseData['choices'][0]['message']['content'])) {
                $reply = $responseData['choices'][0]['message']['content'];
            } else {
                $reply = 'No response from OpenAI API';
            }

            return new JsonResponse(['reply' => $reply]);

        } catch (\Throwable $e) {
            // Captura cualquier error y devuelve una respuesta detallada
            return new JsonResponse([
                'error' => 'API call failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}