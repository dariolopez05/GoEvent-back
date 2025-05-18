<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/api/contact', name: 'api_contact', methods: ['POST'])]
    public function sendEmail(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $messageContent = $data['message'] ?? '';

        if (!$email || !$messageContent) {
            return new JsonResponse(['error' => 'Datos incompletos'], 400);
        }

        $emailMessage = (new Email())
            ->from('goeventmail@gmail.com') 
            ->to('goeventmail@gmail.com')   
            ->replyTo($email)               
            ->subject('Nuevo mensaje desde formulario de contacto')
            ->html(
                "<p><strong>Nombre:</strong> $name</p>" .
                "<p><strong>Email:</strong> $email</p>" .
                "<p><strong>Teléfono:</strong> $phone</p>" .
                "<p><strong>Mensaje:</strong><br/>$messageContent</p>"
            );

        try {
            $mailer->send($emailMessage);
            return new JsonResponse(['message' => 'Email enviado correctamente']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Error al enviar email',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
