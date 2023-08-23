<?php

namespace App\Controller;

use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailController extends AbstractController
{

    private MailerService $mailerService;
    public function __construct(
        MailerService $mailerService
    ) {
        $this->mailerService = $mailerService;
    }

    #[Route('/emails', name: 'app_mail')]
    public function index( Request $request,MailerService $mailer): Response
    {
        $this->mailerService->sendEmail(
            content: $this->renderView('mail/reservation_cantine_confirmation.html.twig',[
                'reservationCantine' => 'test'
            ])
        );
        return new Response("mregel");
    }
}
