<?php

namespace App\Command;

namespace App\Command;
use App\Repository\ReservationSalleRepository;
use App\Service\MailerService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SendReservationSalleRemindersCommand extends Command
{
    protected static $defaultName = 'app:send-reservation-salle-reminders';

    private ReservationSalleRepository $reservationSalleRepository;
    private MailerService $mailerService;

    private $twig;

    public function __construct(ReservationSalleRepository $reservationSalleRepository, MailerService $mailerService, Environment $twig, ParameterBagInterface $params)
    {
        $this->reservationSalleRepository = $reservationSalleRepository;
        $this->mailerService = $mailerService;
        $this->params = $params;
        $this->twig = $twig;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Sends reservation reminders for the next hour.');
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $now = (new \DateTime())->add(new \DateInterval('PT1H'));
            $oneHourLater = (new \DateTime())->add(new \DateInterval('PT2H'));
            $reservations = $this->reservationSalleRepository
                ->createQueryBuilder('r')
                ->where('r.dateDebut BETWEEN :now AND :oneHourLater')
                ->andWhere('r.statut = :status')
                ->setParameters([
                    'now' => $now,
                    'oneHourLater' => $oneHourLater,
                    'status' => 'Réservé',
                ])
                ->getQuery()
                ->getResult();

            if (empty($reservations)) {
                $output->writeln('No reservations found for the next hour.');
                return Command::SUCCESS;
            }
            $logoUrl = $this->params->get('images_directory') . '/logo.png';
            foreach ($reservations as $reservationSalle) {
                $htmlContent = $this->twig->render(
                    'emails/reservation_salle_confirmation.html.twig',
                    [
                        'reservationSalle' => $reservationSalle,
                        'logoUrl' => $logoUrl
                    ]
                );
                $this->mailerService->sendEmail(to: $reservationSalle->getUser()->getEmail(), content: $htmlContent, subject: 'Confirmation de la reservation de salle de reunion ');}
            $output->writeln('Reservation reminders sent successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}