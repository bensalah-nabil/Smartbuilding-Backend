<?php

namespace App\Controller;

use App\Entity\ReservationSalle;
use App\Entity\User;
use App\Event\ReservationSalleCreatedEvent;
use App\Repository\ReservationSalleRepository;
use App\Repository\SalleReunionRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/reservationSalle')]
class ReservationSalleController extends AbstractController
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher, private readonly EntityManagerInterface $manager){}
    #[Route('', name: 'reservation_salle_list', methods: ['GET'])]
    public function getAllReservations(ReservationSalleRepository $reservationSalleRepository,
                                       SerializerInterface $serializer)
    : Response
    {
        $reservations = $reservationSalleRepository->findAll();
        $jsonReservationsList = $serializer->serialize($reservations, 'json', ['groups' => ['user','reservations:salle']]);

        return new Response($jsonReservationsList,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json',
                'Access-Control-Allow-Origin', '*']);
    }
    #[Route('/{id<\d+>}', name: 'reservation_salle_show', methods: ['GET'])]
    public function getDetailReservations(ReservationSalle $reservationSalle, SerializerInterface $serializer)
    : JsonResponse
    {
        $jsonReservation = $serializer->serialize($reservationSalle,'json', ['groups' => 'reservations:salle']);
        return new JsonResponse($jsonReservation, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }
    #[Route("/{id<\d+>}/salle", name:"salle", methods: ['GET'])]
    public function getSalle(ReservationSalle $reservationSalle)
    : JsonResponse
    {
        $salle = $reservationSalle->getSalle();
        return $this->json(['salle' => $salle],200,[], ['groups' => 'salle']);
    }

    #[Route('/user/{id<\d+>}', name: 'reservation_salle_show', methods: ['GET'])]
    public function getResSalleByUserId(User $user, SerializerInterface $serializer)
    : JsonResponse
    {
        $reservations = $user->getReservationSalle();
        $serializedRes = $serializer->serialize($reservations, 'json', ['groups' => ['user','reservations:salle']]);
        return new JsonResponse($serializedRes, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
        );
    }

    /**
     * @throws Exception
     */
    #[Route('', name: 'reservation_salle_create', methods: ['POST'])]
    public function create(Request $request,
                           SerializerInterface $serializer,
                           SalleReunionRepository $salleRepository,
                           UserRepository $userRepository,
                           UrlGeneratorInterface $urlGenerator,
                           NotificationService $notificationService)
    : Response
    {
        $reservationSalle = $serializer->deserialize($request->getContent(), ReservationSalle::class,'json');
       // $reservationSalle->setUser($userRepository->find(json_decode($request->getContent())->userId));
        $salle = $salleRepository->find(json_decode($request->getcontent())->salleDeReunionId);
        $user = $userRepository->find(json_decode($request->getContent())->userId);
        if(!$salle){
            throw new Exception( "no such room with this Id");
        }
        $reservationSalle->setSalle($salle);
        $reservationSalle->setUser($user);
        $this->manager->persist($reservationSalle);
        $this->manager->flush();
        // scheduling of e-mail
        $event = new ReservationSalleCreatedEvent($reservationSalle);
        $this->dispatcher->dispatch($event, ReservationSalleCreatedEvent::NAME);
        //Response
        $jsonReservation = $serializer->serialize($reservationSalle, 'json',['groups' => 'reservations:salle']);
        $location = $urlGenerator->generate('reservation_salle_show',['id' => $reservationSalle->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

        $dateString = $reservationSalle->getDateDebut();
        $format = 'Y-m-d H:i:s'; // Adjust the format based on the actual format of the date string
        $dateDebut = DateTime::createFromFormat($format, $dateString);
        $formattedTime = $dateDebut->format('H:i');
        $salleNom = $reservationSalle->getSalle()->getNom();
        $notificationService->createSalleNotification(
            $reservationSalle->getUserId(),
            'Reservation',
            'Vous avez une réservation de la salle ' . $salleNom . ' à ' . $formattedTime,
            $reservationSalle->getId(),
            $dateString
        );

        return new Response($jsonReservation,Response::HTTP_CREATED,["Location" => $location]
        );
    }
    #[Route('/{id<\d+>}', name: 'reservation_salle_update', methods: ['PUT'])]
    public function updateReservation(Request $request,
                                      SerializerInterface $serializer,
                                      ReservationSalle $currentReservation,
                                      EntityManagerInterface $em)
    : JsonResponse
    {
        $updatedReservation = $serializer->deserialize(
            $request->getContent(),
            ReservationSalle::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentReservation]);
        $em->persist($updatedReservation);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}', name: 'reservation_salle_delete', methods: ['DELETE'])]
    public function delete(ReservationSalle $reservationSalle,
                           EntityManagerInterface $manager)
    : JsonResponse
    {
        $manager->remove($reservationSalle);
        $manager->flush();

        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }

}
