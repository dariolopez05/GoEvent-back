<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TicketmasterController extends AbstractController
{
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
