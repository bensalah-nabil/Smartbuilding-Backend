<?php

namespace App\Service;

use App\Entity\NotificationIncident;
use App\Entity\NotificationReservationCantine;
use App\Entity\NotificationReservationSalle;
use App\Repository\IncidentRepository;
use App\Repository\ReservationCantineRepository;
use App\Repository\ReservationSalleRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\RouterInterface;

class NotificationService {
    private EntityManagerInterface $entityManager;
    private ReservationCantineRepository $cantineRepository;
    private ReservationSalleRepository $salleRepository;
    private IncidentRepository $incidentRepository;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        private ReservationCantineRepository $cantineRepo,
        private ReservationSalleRepository $salleRepo,
        private IncidentRepository $incidentRepo,
        private UserRepository $userRepo,
        private RouterInterface $router

) {
        $this->entityManager = $entityManager;
        $this->cantineRepository = $cantineRepo;
        $this->salleRepository = $salleRepo;
        $this->incidentRepository = $incidentRepo;
        $this->userRepository = $userRepo;
        $this->router = $router;
    }

    /**
     * @throws \Exception
     */
    public function createCantineNotification($userId , $sujet , $message , $Id): void
    {
        try {
            $timezone = new DateTimeZone('Africa/Tunis');
            $now = new DateTime('now', $timezone);
            $notification = new NotificationReservationCantine();
            $notification->setDateEnvoie($now);
            $notification->setSujet($sujet);
            $notification->setMessage($message);
            $notification->setStatut(false);
            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new \Exception('User not found.');
            }
            $notification->setUser($user);

            $notification->setModule('Cantine');

            $reservation = $this->cantineRepository->find($Id);
            if (!$reservation) {
                throw new \Exception('Reservation not found.');
            }
            $notification->setReservation($reservation);

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }catch (\Exception $e) {
            throw new \Exception('Error creating Cantine notification: ' . $e->getMessage());
        }
    }
    public function createIncidentNotification($userId , $sujet , $message , $Id): void
    {
        try{
            $timezone = new DateTimeZone('Africa/Tunis');
            $now = new DateTime('now', $timezone);
            $notification = new NotificationIncident();
            $notification->setDateEnvoie($now);
            $notification->setSujet($sujet);
            $notification->setMessage($message);
            $notification->setStatut(false);

            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new \Exception('User not found.');
            }
            $notification->setUser($user);

            $notification->setModule('Incident');

            $incident = $this->incidentRepository->find($Id);
            if (!$incident) {
                throw new \Exception('Reservation not found.');
            }
            $notification->setIncident($incident);

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }catch (\Exception $e) {

            throw new \Exception('Error creating Cantine notification: ' . $e->getMessage());
        }
    }

    public function createSalleNotification($userId , $sujet , $message , $Id , $date): void
    {
        try{
            $format = 'Y-m-d H:i:s'; // Adjust the format based on the actual format of the date string
            $dateDebut = DateTime::createFromFormat($format, $date);
            $dateDebut->modify('-30 minutes');
            $notification = new NotificationReservationSalle();
            $notification->setDateEnvoie($dateDebut);
            $notification->setSujet($sujet);
            $notification->setMessage($message);
            $notification->setStatut(false);

            $user = $this->userRepository->find($userId);
            if (!$user) {
                throw new \Exception('User not found.');
            }
            $notification->setUser($user);
            $notification->setModule('Salle');
            $reservation = $this->salleRepository->find($Id);
            if (!$reservation) {
                throw new \Exception('Reservation not found.');
            }
            $notification->setReservation($reservation);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }catch (\Exception $e) {
            // Handle the exception as per your application's requirements
            // For example, you can log the error or throw a custom exception
            // or return an error response to the client.
            // You can also customize the error messages based on the specific exception.
            throw new \Exception('Error creating Cantine notification: ' . $e->getMessage());
        }
    }

    public function createNotificationforResponsableMaintenance($sujet , $message , $Id): void
    {
        $users = $this->userRepository->findByRole('ROLE_RESP_MAINTENANCE');
        foreach ($users as $user){
            try {
                $this->createIncidentNotification($user->getId(), $sujet, $message, $Id);
            }catch (\Exception $e){
                throw new \Exception('Error creating Incident notification: ' . $e->getMessage());
            }
        }
    }

    public function publish(HubInterface $hub): JsonResponse
    {
        // Mercure Resource (should be unique per user)
        $topic = 'demo-topic';

        // Data pushed to the Mercure Resource
        $arrayData = [
            'datetime' => (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM),
            'timezone' => 'UTC',
        ];
        $jsonData = json_encode($arrayData, JSON_THROW_ON_ERROR);

        // Mercure Data Wrapper
        $update = new Update($topic, $jsonData);
        // Mercure call
        $hub->publish($update);

        // Confirm data has been published
        return new JsonResponse(json_encode([
            'status' => 'published',
            'topic' => $topic,
            'data' => $arrayData,
        ], JSON_THROW_ON_ERROR));
    }
}