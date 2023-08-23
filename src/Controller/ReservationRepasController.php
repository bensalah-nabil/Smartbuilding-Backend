<?php

namespace App\Controller;

use App\Entity\ReservationRepas;
use App\Repository\RepasParJourRepository;
use App\Repository\ReservationCantineRepository;
use App\Repository\ReservationRepasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/reservationRepas')]
class ReservationRepasController extends AbstractController
{
    #[Route('', name: 'reservation_repas_list', methods: ['GET'])]
    public function getAllReservations(ReservationRepasRepository $reservationRepasRepository,SerializerInterface $serializer)
    : JsonResponse
    {
        $reservations = $reservationRepasRepository->findAll();
        $jsonReservationsList = $serializer->serialize($reservations, 'json', ['groups' => ['menu:food','repas:reservations']]);

        return new JsonResponse($jsonReservationsList,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json',
                'Access-Control-Allow-Origin', '*'],
            true);

    }

    #[Route('/{id<\d+>}', name: 'reservation_repas_show', methods: ['GET'])]
    public function getDetailReservations(ReservationRepas $reservationRepas, SerializerInterface $serializer)
    : JsonResponse
    {
        $jsonReservation = $serializer->serialize($reservationRepas,'json', ['groups' => 'repas:reservations']);
        return new JsonResponse($jsonReservation, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route('/{id<\d+>}/info', name: 'repas_by_reservation_show', methods: ['GET'])]
    public function getRepasByReservation(ReservationRepas $reservationRepas)
    : JsonResponse
    {
        $rpj = $reservationRepas->getRepas()->getFood()->getName();
        $reservationCantine = $reservationRepas->getReservation()->getDateReservation();
        return $this->json([$rpj,$reservationCantine],200,[], ['groups' => 'menu:food']);
    }
    #[Route('', name: 'reservation_repas_create', methods: ['POST'])]
    public function create(Request $request,
                           EntityManagerInterface $manager,
                           RepasParJourRepository $jourRepository,
                           ReservationCantineRepository $reservationCantineRepository)
    : Response
    {
        foreach (json_decode($request->getContent()) as $repasParJour)
        {
            $quantity = $repasParJour->quantity;
            $repasParJourId = $repasParJour->repasParJourId;
            $reservationCantineId = $repasParJour->reservationCantineId;

            $rpj = $jourRepository->find($repasParJourId);
            $rc = $reservationCantineRepository->find($reservationCantineId);

            $rr = (new ReservationRepas())
                ->setQuantity($quantity)
                ->setRepas($rpj)
                ->setReservation($rc);
            $manager->persist($rr);
        }

        $manager->flush();
        return $this->redirectToRoute('reservation_repas_list');
    }

    #[Route('/{id<\d+>}', name: 'reservation_repas_delete', methods: ['DELETE'])]
    public function delete(ReservationRepas $reservationRepas,
                           EntityManagerInterface $manager)
    : JsonResponse
    {
        $manager->remove($reservationRepas);
        $manager->flush();

        return new JSONResponse('', Response::HTTP_NO_CONTENT);

    }
}

