<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class ContactController extends AbstractController
{
    #[Route('/api/contact', name: 'api_contact', methods: ['POST'])]
    public function sendEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $messageContent = $data['message'] ?? '';

        if (!$email || !$messageContent) {
            return new JsonResponse(['error' => 'Datos incompletos'], 400);
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';            
            $mail->SMTPAuth = true;
            $mail->Username = 'goeventmail@gmail.com';     
            $mail->Password = 'ozzuatpotimupukq';        
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($email, $name);
            $mail->addAddress('goeventmail@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = 'Nuevo mensaje desde formulario de contacto';
            $mail->Body    = "<p><strong>Nombre:</strong> $name</p>
                              <p><strong>Email:</strong> $email</p>
                              <p><strong>Teléfono:</strong> $phone</p>
                              <p><strong>Mensaje:</strong><br/>$messageContent</p>";

            $mail->send();
            return new JsonResponse(['message' => 'Email enviado correctamente']);
        } catch (Exception $e) {
            return new JsonResponse(['error' => "No se pudo enviar el mensaje. Mailer Error: {$mail->ErrorInfo}"], 500);
        }
    }
}
