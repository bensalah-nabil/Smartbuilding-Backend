<?php

namespace App\Command;

use App\Service\MailerService;
use App\Repository\ReservationCantineRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class SendReservationCantineRemindersCommand extends Command
{
    protected static $defaultName = 'app:send-reservation-cantine-reminders';

    private ReservationCantineRepository $reservationCantineRepository;
    private MailerService $mailerService;
    private Environment $twig;
    public function __construct(ReservationCantineRepository $reservationCantineRepository, MailerService $mailerService, Environment $twig)
    {
        $this->reservationCantineRepository = $reservationCantineRepository;
        $this->mailerService = $mailerService;
        $this->twig = $twig;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Sends reservation reminders for the next hour.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateTime = new \DateTime();
//        if ($dateTime->format('H:i') !== '12:45') {
//            $output->writeln('Reservation reminders can only be sent at 12:45 pm.');
//            return Command::SUCCESS;
//        }
        try {
            $reservations = $this->reservationCantineRepository->findBy(['statut' => 'Réservé']);
            if (empty($reservations)) {
                $output->writeln('No reservations found');
                return Command::SUCCESS;
            }
            foreach ($reservations as $reservationCantine) {
                $htmlContent = $this->twig->render(
                    'emails/reservation_cantine_confirmation.html.twig',
                    [
                        'reservationCantine' => $reservationCantine
                    ]
                );
                $this->mailerService->sendEmail(
                    to: $reservationCantine->getUser()->getEmail(),
                    content: $htmlContent,
                    subject: 'Reservation Cantine Confirmation'
                );
            }
            $output->writeln('Reservation reminders sent successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
