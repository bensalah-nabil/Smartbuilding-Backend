<?php

namespace App\Controller;

use App\Entity\ReservationCantine;
use App\Entity\ReservationSalle;
use App\Entity\User;
use App\Service\MSGraphService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PDFservice;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;


#[Route('api/v1/users')]
class UserController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface          $manager,
        private readonly SerializerInterface             $serializer,
        private readonly UserRepository                  $userRepository,
        private readonly MSGraphService                  $graphService )
    { }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), 200,[],['groups'=> 'user']);
    }

    #[Route('/current/notification', name: 'user_notification_show', methods: ['GET'])]
    public function getCurrentUserNotification(): JsonResponse
    {
        $jsonNotificationArray = $this->serializer->serialize(($this->userRepository->find($this->getUser()))->getNotifications(),'json',['groups'=>'notification']);
        return new JsonResponse($jsonNotificationArray ,Response::HTTP_OK,
        ['Content-Type' => 'application/json'],
        true
        );
    }

    #[Route('/current/incidentNotification', name: 'user_incident_notification_show', methods: ['GET'])]
    public function getCurrentUserIncidentNotification(): JsonResponse
    {
        $notificationArray = ($this->userRepository->find($this->getUser()))->getNotifications();
        $incidentNotificationArray = [];
        foreach( $notificationArray as $notification ){
            $module = $notification->getModule();
            if ($module == "incident"){
                $incidentNotificationArray[] = $notification;
            }
        }
        $result = $this->serializer->serialize($incidentNotificationArray,'json',['groups'=>'notification']);
        return new JsonResponse($result ,Response::HTTP_OK,
        ['Content-Type' => 'application/json'],
        true
        );
    }

    #[Route('/current/cantineNotification', name: 'user_cantine_cantine_show', methods: ['GET'])]
    public function getCurrentUserCantineNotification(): JsonResponse
    {
        $notificationArray = ($this->userRepository->find($this->getUser()))->getNotifications();
        $cantineNotificationArray = [];
        foreach( $notificationArray as $notification ){
            $module = $notification->getModule();
            if ($module == "cantine"){
                $cantineNotificationArray[] = $notification;
            }
        }
        $result = $this->serializer->serialize($cantineNotificationArray,'json',['groups'=>'notification']);
        return new JsonResponse($result ,Response::HTTP_OK,
        ['Content-Type' => 'application/json'],
        true
        );
    }

    #[Route('/current/salleNotification', name: 'user_salle_notification_show', methods: ['GET'])]
    public function getCurrentUserSalleNotification(): JsonResponse
    {
        $notificationArray = ($this->userRepository->find($this->getUser()))->getNotifications();
        $salleNotificationArray = [];
        foreach( $notificationArray as $notification ){
            $module = $notification->getModule();
            if ($module == "salle"){
                $salleNotificationArray[] = $notification;
            }
        }
        $result = $this->serializer->serialize($salleNotificationArray,'json',['groups'=>'notification']);
        return new JsonResponse($result ,Response::HTTP_OK,
        ['Content-Type' => 'application/json'],
        true
        );
    }

    #[Route('/current', name: 'user_show', methods: ['GET'])]
    public function getCurrentUser(): Response
    {
        return $this->json($this->userRepository->find($this->getUser()), Response::HTTP_OK, [], ['groups' => 'user']);
    }

    #[Route('/{id<\d+>}', name: 'user_show_by_Id', methods: ['GET'])]
    public function getUserById(User $user,UserRepository $userRepository): JsonResponse

    {
        $user = $userRepository->find($user);
        if (!$user){
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }
        $jsonSalle = $this->serializer->serialize($user,'json',['groups'=>'user']);
        return new JsonResponse($jsonSalle, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route('/updateRole', name: 'user_role_update', methods: ['PUT'])]
    public function updateRole(Request $request): JsonResponse
    {
        $authorizationHeader = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);
        $role = $this->graphService->getRole($token);
        $user = $this->userRepository->find($this->getUser());
        if ($user) {
            $user->setRoles([$role['value']]);
            $this->manager->flush();
            return $this->json($this->getUser(), Response::HTTP_OK, [], ['groups' => 'user']);
        } else {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'user_create', methods: ['POST'])]
    public function createUser(): JsonResponse
    {
        $user = $this->userRepository->find($this->getUser());
        $existantUser = $this->userRepository->findOneBy(['uuid' => $user->getUuid()]);
        if (!$existantUser){
            $this->manager->persist($user);
            $this->manager->flush();
            return $this->json(Response::HTTP_CREATED);
        }else{
            return $this->json(Response::HTTP_OK);
        }
    }

    //Cantine
    #[Route('/cancelCantine/{id<\d+>}', name: 'reservation_cantine_cancel_reservation')]
    public function cancelReservationCantine(ReservationCantine $currentReservation): RedirectResponse
    {
        $currentReservation->setStatut("Annulé");
        $this->manager->flush();
        return new RedirectResponse($_ENV['FRONT_URL'].'/dashboard/cantine');
    }
    #[Route('/confirmCantine/{id<\d+>}', name: 'reservation_cantine_confirm_reservation')]
    public function confirmReservationCantine(ReservationCantine $currentReservation): RedirectResponse
    {
        $currentReservation->setDateConfirmation(new \DateTime('now', new \DateTimeZone('Africa/Tunis')));
        $currentReservation->setStatut("Confirmé");
        $this->manager->flush();
        return new RedirectResponse($_ENV['FRONT_URL'].'/dashboard/cantine');
    }

    // Salle
    #[Route('/cancelSalle/{id<\d+>}', name: 'reservation_salle_cancel_reservation')]
    public function cancelReservationSalle(ReservationSalle $currentReservation): RedirectResponse
    {
        $currentReservation->setStatut("Annulé");
        $this->manager->flush();
        return new RedirectResponse($_ENV['FRONT_URL'].'/reunion/reservation');
    }

    #[Route('/confirmSalle/{id<\d+>}', name: 'reservation_salle_confirm_reservation')]
    public function confirmReservationSalle(ReservationSalle $currentReservation, PDFservice $PDFservice ): RedirectResponse
    {
        $filename = 'reservation.pdf';
        $currentReservation->setDateConfirmation(new \DateTime('now', new \DateTimeZone('Africa/Tunis')));
        $currentReservation->setStatut("Confirmé");
        $this->manager->flush();
        $html = $this->render('emails/reservation_salle_confirmation.html.twig',
            [
                'reservationSalle' => $currentReservation
            ]);
        $PDFservice->showPDF($html);
        $PDFservice->generateBinairyPDF($html);
        return new RedirectResponse($_ENV['FRONT_URL'].'/reunion/reservation?pdf='.$filename); // Redirect the user to the front URL and provide the filename as a query parameter
    }

    // Account Activation
    #[Route('/{id}/activate', name: 'activate_user', methods: ['POST'])]
    public function activateUser(User $user): JsonResponse
    {
        $user->setIsVerified(1);
        $jsonSalle = $this->serializer->serialize($user,'json',['groups'=>'user']);
        return new JsonResponse($jsonSalle, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route('/{id}/deactivate', name: 'deactivate_user', methods: ['POST'])]
    public function deActivateUser(User $user): JsonResponse
    {
        $user->setIsVerified(0);
        $jsonSalle = $this->serializer->serialize($user,'json',['groups'=>'user']);
        return new JsonResponse($jsonSalle, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }
}
