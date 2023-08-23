<?php

namespace App\Controller;

use App\Entity\RepasParJour;
use App\Repository\RepasParJourRepository;
use App\Repository\FoodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/v1/repasParJour')]

class RepasParJourController extends AbstractController
{
    #[Route('', name: 'list_repas_par_jour', methods: ['GET'])]
    public function getAllAvFoods(RepasParJourRepository $repasParJourRepository): JsonResponse
    {
        return $this->json($repasParJourRepository->findAll(), 200,[], ['groups' => ['menu:food','repas:reservations']]);
    }
    #[Route('/{id<\d+>}', name: 'show_repas_par_jour', methods: ['GET'])]
    public function show(RepasParJour $repasParJour): JsonResponse
    {
        return $this->json($repasParJour,200,[], ['groups' => 'menu:food']);
    }
    #[Route('/{id<\d+>}/reservationRepas', name: 'show_reservation_par_repas', methods: ['GET'])]
    public function getReservationRepas(RepasParJour $repasParJour): JsonResponse
    {
        return $this->json($repasParJour->getReservationRepas(),200,[], ['groups' => 'repas:reservations']);
    }
    #[Route('/{id<\d+>}/reservationCantine', name: 'show_reservation_cantine', methods: ['GET'])]
    public function getReservationCantine(RepasParJour $repasParJour): JsonResponse
    {
        $rr = $repasParJour->getReservationRepas();
        $rc = new ArrayCollection();
        foreach ($rr as $reservationRepas){
            $rc[] = $reservationRepas->getReservation();
        }
        return $this->json($rc,200,[], ['groups' => 'repas:reservations']);
    }
    #[Route('', name: 'create_repas_par_jour', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $repasParJour = new RepasParJour();
        $repasParJour->setStock(5);

        $manager->persist($repasParJour);
        $manager->flush();

        return $this->json($repasParJour);
    }

    #[Route('/{id<\d+>}', name: 'update_repas_par_jour', methods: ['PUT'])]
    public function update(Request $request, RepasParJourRepository $repasParJour, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $repasParJour->setName($data['name']);
        $repasParJour->setDescription($data['description']);
        // Set any other properties

        $manager->flush();

        return $this->json($repasParJour);
    }

    #[Route('/{id<\d+>}', name: 'delete_repas_par_jour', methods: ['DELETE'])]
    public function delete(RepasParJour $repasParJour, EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($repasParJour);
        $manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}




























