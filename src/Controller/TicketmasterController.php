<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

final class TicketmasterController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $_ENV['TICKETMASTER_API_KEY'] ?? '';
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

            $data = $response->toArray(false);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                return $this->json([
                    'error' => 'Error desde Ticketmaster',
                    'codigo_http' => $statusCode,
                    'detalle' => $data,
                ], $statusCode);
            }

            return $this->json($data);
        } catch (\Throwable $e) {
            $this->logger->error('Error al consultar Ticketmaster', [
                'exception' => $e,
                'id' => $id,
            ]);

            return $this->json([
                'error' => 'Error inesperado al contactar con Ticketmaster',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
