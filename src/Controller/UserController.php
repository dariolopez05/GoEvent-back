<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/login', name: 'user_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email y contraseña son obligatorios.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return new JsonResponse(['error' => 'Credenciales inválidas.'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'city' => $user->getCity(),
                'username' => $user->getUsername(),
                'favourites' => $user->getFavorites(),
            ]
        ]);
    }

    #[Route('/user/favourites', name: 'user_add_favourites', methods: ['POST'])]
    public function addFavourites(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $userId = $data['userId'] ?? null;
        $eventIds = $data['eventIds'] ?? [];

        // Verificación: Asegurarse de que eventIds sea un array
        if (!is_array($eventIds)) {
            return new JsonResponse(['error' => 'Favoritos no válidos.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$userId || !is_array($eventIds)) {
            return new JsonResponse(['error' => 'Datos inválidos.'], Response::HTTP_BAD_REQUEST);
        }

        // Buscar al usuario por ID
        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        // Establecer los favoritos del usuario
        $user->setFavorites($eventIds);  // Asignar el array de favoritos actualizado

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Favoritos actualizados correctamente.', 'favorites' => $eventIds]);
    }


}

