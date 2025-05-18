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
public function register(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $username = $data['username'] ?? null;
    $city = $data['city'] ?? null;

    if (!$email || !$password || !$username || !$city) {
        return new JsonResponse(['error' => 'Todos los campos son obligatorios.'], Response::HTTP_BAD_REQUEST);
    }

    // Comprobar si ya existe un usuario con ese email
    $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    if ($existingUser) {
        return new JsonResponse(['error' => 'El correo ya está registrado.'], Response::HTTP_CONFLICT);
    }

    $user = new User();
    $user->setEmail($email);
    $user->setUsername($username);
    $user->setCity($city);

    $hashedPassword = $passwordHasher->hashPassword($user, $password);
    $user->setPassword($hashedPassword);

    try {
        $entityManager->persist($user);
        $entityManager->flush();
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Error al registrar el usuario.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new JsonResponse(['message' => 'Usuario registrado con éxito.'], Response::HTTP_CREATED);
}

}

