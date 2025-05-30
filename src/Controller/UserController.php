<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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

        if (!is_array($eventIds)) {
            return new JsonResponse(['error' => 'Favoritos no válidos.'], Response::HTTP_BAD_REQUEST);
        }

        if (!$userId || !is_array($eventIds)) {
            return new JsonResponse(['error' => 'Datos inválidos.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $user->setFavorites($eventIds);  

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Favoritos actualizados correctamente.', 'favorites' => $eventIds]);
    }

   #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email'], $data['username'], $data['password'], $data['city'])) {
                return new JsonResponse(['error' => 'Faltan campos obligatorios'], 400);
            }

            $email = $data['email'];

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                return new JsonResponse(['error' => 'Este correo ya está registrado.'], 400);
            }

            $user = new User();
            $user->setEmail($email);
            $user->setUsername($data['username']);
            $user->setPassword($data['password']);
            $user->setCity($data['city']);
            $user->setFavorites([]);

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Usuario registrado correctamente']);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Error del servidor: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/user/update', name: 'user_update', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $userId = $data['id'] ?? null;
        $newEmail = $data['email'] ?? null;
        $newUsername = $data['username'] ?? null;
        $newCity = $data['city'] ?? null;

        if (!$userId || !$newEmail || !$newUsername || !$newCity) {
            return new JsonResponse(['error' => 'Faltan campos obligatorios.'], 400);
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado.'], 404);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $newEmail]);
        if ($existingUser && $existingUser->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'El correo ya está en uso.'], 400);
        }

        $user->setEmail($newEmail);
        $user->setUsername($newUsername);
        $user->setCity($newCity);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario actualizado correctamente.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'city' => $user->getCity(),
            ]
        ]);
    }

}

