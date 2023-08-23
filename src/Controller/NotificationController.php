<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\NotificationReservationCantine;
use App\Entity\NotificationReservationSalle;
use App\Repository\NotificationIncidentRepository;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\NotificationIncident;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Mercure\HubInterface;


#[Route('/api/v1/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository, private readonly NotificationService $notifService, private readonly NotificationRepository $notificationRepository)
    { }

    #[Route('/', name: 'all_notification', methods:['GET'])]
    public function getAllNotifications(): Response
    {
        return $this->json($this->notificationRepository->getAllDataFromMultipleTables(), 200,[],['groups'=> 'notification']);
    }

    //"An exception occurred while executing a query: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'talan_db.notification' doesn't exist"
    // #[Route('/{id}', name: 'detail_notification', methods:['GET'])]
    // public function getNotification(Notification $notification): Response
    // {
    //     return $this->json($this->notificationRepository->find($notification), 200,[],['groups'=> 'notification']);
    // }

    #[Route('/{id}/user', name: 'user_notification', methods:['GET'])]
    public function getNotificationUser(): Response
    {
        return $this->json($this->notificationRepository->getDataFromMultipleTablesByUserId(($this->userRepository->find($this->getUser()))->getId()), 200,[],['groups'=> 'notification']);
    }

    #[Route('', name: 'notification_create', methods: ['POST'])]
    public function addNotification( Request $request, SerializerInterface $serializer,EntityManagerInterface $manager ): ?Response {
        try {
            $notification = $serializer->deserialize($request->getContent(), Notification::class,'json');
            if (($notification->getModule()) == 'incident'){
                $incidentNotification =  $serializer->deserialize($request->getContent(), NotificationIncident::class,'json');
                $manager->persist($incidentNotification);
                $manager->flush();
            }elseif(($notification->getModule()) == 'cantine'){
                $cantineNotification =  $serializer->deserialize($request->getContent(), NotificationReservationCantine::class,'json');
                $manager->persist($cantineNotification);
                $manager->flush();
            }elseif(($notification->getModule()) == 'salle'){
                $salleNotification =  $serializer->deserialize($request->getContent(), NotificationReservationSalle::class,'json');
                $manager->persist($salleNotification);
                $manager->flush();
            }
            return new Response($serializer->serialize($incidentNotification || $cantineNotification  || $salleNotification, 'json'), 201);
        } catch (\Exception $e) {
            $message = 'Missing required fields.';
            if ($e->getMessage() === 'Invalid JSON data') {
                $message = 'Invalid JSON data';
            }
            return new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/vu/{id}', name: 'notification_get', methods: ['PATCH'])]
    public function editStatut(Request $request,SerializerInterface $serializer,EntityManagerInterface $manager,$id){
        $module = json_decode($request->getContent(), true);
        $notif = null;
        $data = null;
        switch ($module['module']){
            case 'Salle' :
                $notif = $manager->getRepository(NotificationReservationSalle::class)->find($id);
                $reservation = $notif->getReservation();
                $manager->refresh($reservation);
                $data = $serializer->serialize($reservation,'json',['groups'=>['reservation:notif','salle']]);
                break;
           case 'Cantine' :
                $notif = $manager->getRepository(NotificationReservationCantine::class)->find($id);
                $reservation = $notif->getReservation();
                $manager->refresh($reservation);
                $data = $serializer->serialize($reservation,'json',['groups' => 'reservation:foods']);
                //$data = [$notif->getReservation() , $notif->getReservation()->getReservationRepas()];
                break;
            case 'Incident' :
                $notif = $manager->getRepository(NotificationIncident::class)->find($id);
                $incident = $notif->getIncident();
                $manager->refresh($incident);
                $data = $serializer->serialize($incident, 'json', ['groups' => 'incidents']);
                break;
            default :
                return new Response(null,Response::HTTP_NOT_FOUND);
        }
        if ($notif){
        $notif->setStatut(true);
        $manager->persist($notif);
        $manager->flush();
        return new Response($data,200,[]);
        }else {
            return new Response(null,Response::HTTP_NOT_FOUND);
        }
    }
    #[Route(path: '/publish', name: 'publish', methods: ['GET'])]
    public function publish(HubInterface $hub){
        return($this->notifService->publish($hub));
    }

    #[Route(path: '/notifications', name: 'create_notification', methods: ['POST'])]
    public function createNotification(NotificationService $notificationService): JsonResponse
    {
        // Create the notification
        $notification = $notificationService->createNotification();

        // Publish the notification data to the frontend with the action "create"
        $notificationService->publishNotification('create', $notification);

        // Return a response indicating success
        return new JsonResponse(['status' => 'success']);
    }
}
