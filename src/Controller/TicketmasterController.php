<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class TicketmasterController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private CacheInterface $cache;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->apiKey = $_ENV['TICKETMASTER_API_KEY'] ?? '';
    }

    #[Route('/api/ticketmaster/id', name: 'ticketmaster_by_id', methods: ['GET'])]
    public function getById(Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        if (!$id) {
            return $this->json(['error' => 'ID requerido'], 400);
        }

        $cacheKey = 'ticketmaster_event_' . md5($id);

        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(600);

            $response = $this->httpClient->request('GET', 'https://app.ticketmaster.com/discovery/v2/events', [
                'query' => [
                    'countryCode' => 'ES',
                    'apikey' => $this->apiKey,
                    'id' => $id,
                ],
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Error desde Ticketmaster: ' . $response->getStatusCode());
            }

            return $response->toArray();
        });

        return $this->json($data);
    }
}
