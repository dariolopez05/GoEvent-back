<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class TicketmasterController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $_ENV['TICKETMASTER_API_KEY']; 
    }

    #[Route('/api/ticketmaster/id', name: 'ticketmaster_by_id', methods: ['GET'])]
    public function getById(Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        if (!$id) {
            return $this->json(['error' => 'ID requerido'], 400);
        }

        try {
            $response = $this->httpClient->request('GET', 'https://app.ticketmaster.com/discovery/v2/events', [
                'query' => [
                    'countryCode' => 'ES',
                    'apikey' => $this->apiKey,
                    'id' => $id,
                ],
            ]);

            return $this->json($response->toArray());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
