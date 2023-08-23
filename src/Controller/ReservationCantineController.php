<?php

namespace App\Controller;

use App\Entity\ReservationCantine;
use App\Entity\User;
use App\Event\ReservationCantineCreatedEvent;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ReservationCantineRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/v1/reservationCantine')]
class ReservationCantineController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $manager
    ){ }
    #[Route('', name: 'reservation_cantine_list', methods: ['GET'])]
    public function getAllReservations(ReservationCantineRepository $reservationCantineRepository, SerializerInterface $serializer)
    : Response
    {
        $reservations = $reservationCantineRepository->findAll();
        $jsonReservationsList = $serializer->serialize($reservations, 'json', ['groups' => ['user','reservations','repas:reservations']]);

        return new Response($jsonReservationsList,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json',
                'Access-Control-Allow-Origin', '*'],true);
    }

    #[Route('/{id<\d+>}', name: 'reservation_cantine_show', methods: ['GET'])]
    public function getDetailReservations(ReservationCantine $reservationCantine, SerializerInterface $serializer)
    : JsonResponse
    {
        $jsonReservation = $serializer->serialize($reservationCantine,'json', ['groups' => ['user','reservations','repas:reservations']]);
        return new JsonResponse($jsonReservation, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route('/user/{id<\d+>}', name: 'show_reservation_by_user', methods: ['GET'])]
    public function getResByUserId(User $user, SerializerInterface $serializer)
    : JsonResponse
    {
        $reservations = $user->getReservationCantines();
        $serializedRes = $serializer->serialize($reservations, 'json', ['groups' => ['user','repas:reservations']]);
        return new JsonResponse($serializedRes, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
        );
    }

    #[Route("/{id<\d+>}/reservationRepas", name:"reservationRepas", methods: ['GET'])]
    public function getReservationRepas($id, ReservationCantineRepository $reservationCantineRepository)
    : JsonResponse
    {
        $reservationCantine = $reservationCantineRepository->find($id);

        if (!$reservationCantine) {
            throw $this->createNotFoundException('La réservation de cantine avec l\'identifiant '.$id.' n\'a pas été trouvée.');
        }

        $reservationRepas = $reservationCantine->getReservationRepas();
        return $this->json( $reservationRepas,200,[], ['groups' => 'repas:reservations']);
    }

    #[Route("/{id<\d+>}/repasParJours", name:"repasParJours", methods: ['GET'])]
    public function getRepasParJour($id, ReservationCantineRepository $reservationCantineRepository)
    : JsonResponse
    {
        $reservationCantine = $reservationCantineRepository->find($id);

        if (!$reservationCantine) {
            throw $this->createNotFoundException('La réservation de cantine avec l\'identifiant '.$id.' n\'a pas été trouvée.');
        }

        $reservationRepas = $reservationCantine->getReservationRepas();
        $repasParJourArray = new ArrayCollection();
        foreach ($reservationRepas as $reservation){
            $rpj = $reservation->getRepas();
            $repasParJourArray[] = $rpj;
        }
        return $this->json($repasParJourArray,200,[], ['groups' => 'menu:food']);
    }

    #[Route("/{id<\d+>}/repas", name:"repas", methods: ['GET'])]
    public function getRepas($id, ReservationCantineRepository $reservationCantineRepository)
    : JsonResponse
    {
        $reservationCantine = $reservationCantineRepository->find($id);
        if (!$reservationCantine) {
            throw $this->createNotFoundException('La réservation de cantine avec l\'identifiant '.$id.' n\'a pas été trouvée.');
        }
        $reservationRepas = $reservationCantine->getReservationRepas();
        $foods = new ArrayCollection();
        foreach ($reservationRepas as $reservation){
            $food = $reservation->getRepas()->getFood();
            $foods[] = $food;
        }
        return $this->json($foods,200,[], ['groups' => 'food']);
    }

    #[Route('', name: 'reservation_cantine_create', methods: ['POST'])]
    public function create(Request $request,
                           SerializerInterface $serializer,
                           UserRepository $userRepository,
                           UrlGeneratorInterface $urlGenerator,
                           NotificationService $notificationService)
    : Response
    {
        $reservationCantine = $serializer->deserialize($request->getContent(), ReservationCantine::class,'json');
        $user = $userRepository->find($this->getUser());
        $reservationCantine->setUser($user);
        $dateReservation = $reservationCantine->getDateReservation();
        $this->manager->persist($reservationCantine);
        $this->manager->flush();

        // scheduling of e-mail
        $event = new ReservationCantineCreatedEvent($reservationCantine);
        $this->dispatcher->dispatch($event, ReservationCantineCreatedEvent::NAME);
        //Response
        $jsonReservation = $serializer->serialize($reservationCantine, 'json',['groups' => ['user','reservations','repas:reservations']]);
        $location = $urlGenerator->generate('reservation_cantine_show',['id' => $reservationCantine->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

        $notificationService->createCantineNotification(
            $user->getId(),
            'Reservation',
            'Confirmation de votre commande',
            $reservationCantine->getId()
        );

        return new Response($jsonReservation, Response::HTTP_CREATED, ["Location" => $location]);
    }
    #[Route('/{id<\d+>}', name: 'reservation_cantine_update', methods: ['PUT'])]
    public function updateReservation(Request $request,
                               SerializerInterface $serializer,
                               ReservationCantine $currentReservation,
                               )
    : JsonResponse
    {
        $updatedReservation = $serializer->deserialize(
            $request->getContent(),
            ReservationCantine::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentReservation]);
        $this->manager->persist($updatedReservation);
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}', name: 'reservation_cantine_delete', methods: ['DELETE'])]
    public function delete(ReservationCantine $reservationCantine)
    : JsonResponse
    {
        $this->manager->remove($reservationCantine);
        $this->manager->flush();
        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }
}
