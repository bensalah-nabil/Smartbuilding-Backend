<?php

namespace App\Controller;

use App\Entity\SalleReunion;
use App\Repository\EquipementRepository;
use App\Repository\ReservationSalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\UploaderService;
use App\Repository\SalleReunionRepository;


#[Route('/api/v1/salles')]
class SalleController extends AbstractController
{
    private UploaderService $uploaderService;
    public function __construct(
        UploaderService $uploaderService
    ) {
        $this->uploaderService = $uploaderService;
    }
    #[Route('', name: 'salles_list', methods: ['GET'])]
    public function getAllRooms(SalleReunionRepository $salleRepository): JsonResponse
    {
        return $this->json($salleRepository->findAll(), 200,[],['groups'=>['salle','salle:equipement']]);
    }
    #[Route('/{id<\d+>}', name: 'salle_show', methods: ['GET'])]
    public function getDetailSalles(SalleReunion $salle, SerializerInterface $serializer): JsonResponse
    {
        $jsonSalle = $serializer->serialize($salle,'json',['groups'=>['salle','salle:equipement']]);
        return new JsonResponse($jsonSalle, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }
    #[Route('/{nom<[a-zA-Z]+>}', name: 'salle_show_by_nom', methods: ['GET'])]
    public function getDetailSalle(String $nom, SalleReunionRepository $reunionRepository, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($reunionRepository->findOneBy(['nom' => $nom]), 'json', ['groups'=>['salle','salle:equipement']]);
        return new JsonResponse($json, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

    #[Route("/{id<\d+>}/reservation", name:"reservation", methods: ['GET'])]
    public function getReservation($id,SalleReunionRepository $salleRepository)
    : JsonResponse
    {
        $salle = $salleRepository->find($id);
        if (!$salle) {
            throw $this->createNotFoundException('La salle avec l\'identifiant '.$id.' n\'a pas été trouvée.');
        }
        $reservationSalles = ($salle->getReservationSalles())->toArray();
        return $this->json($reservationSalles,200,[], ['groups' => 'reservations:salle']);
    }

    #[Route("/{id<\d+>}/reservationDate", name:"reservationDate", methods: ['GET'])]
    public function getReservationByDate($id,SalleReunionRepository $salleRepository,ReservationSalleRepository $reservationSalleRepository)
    : JsonResponse
    {
        $salle = $salleRepository->find($id);
        if (!$salle) {
            throw $this->createNotFoundException('La salle avec l\'identifiant '.$id.' n\'a pas été trouvée.');
        }
        $date = (new \DateTime())->format('Y-m-d');
        $reservationSalles = ($salle->getReservationSalles())->toArray();
        //$reservationSallesByDate = $reservationSalleRepository->find(['dateDebut'=>$date]);
        $reservationByIdByDate = new ArrayCollection();
        foreach ($reservationSalles as $reservation) {
            $dateDebut = (new \DateTime($reservation->getDateDebut()))->format('Y-m-d');
            if ($dateDebut == $date){
                $reservationByIdByDate->add($reservation);
            }
        }
        return $this->json($reservationByIdByDate,200,[], ['groups' => 'reservations:salle']);
    }
    #[Route("/{id<\d+>}/equipements", name:"equipements", methods: ['GET'])]
    public function getEquipements($id, SalleReunionRepository $salleRepository): JsonResponse
    {
        try {
            $salle = $salleRepository->find($id);
            if (!$salle) {
                return $this->json(['message' => 'Salle with id ' . $id . ' does not exist in the database'], 404);
            }
            return $this->json($salle->getEquipements(),200,[],['groups' => 'equipement']);
        } catch (Exception) {
            return $this->json(['message' => 'An error occurred while retrieving the equipments for salle with id ' . $id], 500);
        }
    }
    #[Route('/{id<\d+>}', name: 'salle_delete', methods: ['DELETE'])]
    public function delete(SalleReunion $salleReunion,
                           EntityManagerInterface $manager): JsonResponse
    {
        $manager->remove($salleReunion);
        $manager->flush();

        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }
    #[Route('', name: 'salle_create', methods: ['POST'])]
    public function addSalle(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        EquipementRepository $equipementRepository,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        try {
            $salle = $serializer->deserialize($request->request->get('salle'), SalleReunion::class,'json');
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $salleExistant = $manager->getRepository(SalleReunion::class)->findOneBy(['nom' => $salle->getNom()]);
            if ($salleExistant){
                throw new Exception(sprintf('salle with name "%s" already exists.', $salle->getNom()));
            }
            $equipements = json_decode($request->request->get('equipements'));
            foreach ($equipements as $equipement){
                $salle->addEquipement($equipementRepository->find($equipement->id));
            }
            $uploadedFile = $request->files->get('image');
            if (!$uploadedFile) {
                throw new Exception('No image uploaded.');
            }
            $newFileName = $this->uploaderService->getFileName($uploadedFile);
            $directoryPath = $this->getParameter('salles_directory');
            $relativePath = $this->uploaderService->getPath($newFileName,$directoryPath,$uploadedFile);
            $image = (new Image())
                ->setPath($relativePath);
            $salle->setImage($image);

            $manager->persist($salle);
            $manager->persist($image);
            $manager->flush();

            $jsonSalle = $serializer->serialize($salle, 'json', ['groups'=>'salle']);
            $statusCode = $salle->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate('salle_show', ['id' => $salle->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new Response($jsonSalle, $statusCode, ['Location' => $location]);

        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    #[Route('/update', name: 'salle_update', methods: ['POST'])]
    public function updateSalle(
        Request $request,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        EquipementRepository $equipementRepository
    ): Response {
        try {
            $salle = $serializer->deserialize($request->request->get('salle'), SalleReunion::class,'json');
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            $id = json_decode($request->request->get('salle'))->id;
            $salleExistant = $manager->getRepository(SalleReunion::class)->findOneBy(['id' => $id]);
            if (!$salleExistant){
                return new JsonResponse(['message' => 'Room not found.'], Response::HTTP_NOT_FOUND);
            }
            $salleExistant->setNom($salle->getNom())
                ->setCapacite(($salle->getCapacite()))
                ->setStatut(($salle->getstatut()))
                ->setEmplacement($salle->getEmplacement());
            $equipementNouveau = json_decode($request->request->get('equipements'));
            $equipementIdNouveau = new ArrayCollection();
            foreach ($equipementNouveau as $equipement){
                $equipementIdNouveau [] = $equipement->id ;
            }
            $equipements = $salleExistant->getEquipements();
            if ($equipements){
                $equipementExistant = $equipements->toArray();
                $equipementIdExistant = new ArrayCollection();
                foreach ($equipementExistant as $equipement){
                    $equipementIdExistant [] = $equipement->getId() ;
                }
                foreach ($equipementNouveau as $equipement){
                    if (!in_array($equipement->id, $equipementIdExistant->toArray())){
                        $equipement = $equipementRepository->find($equipement->id);
                        $equipement->addSalleReunion($salleExistant);
                        $manager->flush();
                    }
                }
                foreach ($equipementExistant as $equipement){
                    if (!in_array($equipement->getId(), $equipementIdNouveau->toArray())){
                        $equipement->removeSalleReunion($salleExistant);
                    }
                }
            }else{
                foreach ($equipementNouveau as $equipement){
                    $equipement = $equipementRepository->find($equipement->id);
                    $equipement->addSalleReunion($salleExistant);
                    $manager->flush();
                }
            }
            $uploadedFile = $request->files->get('image');
            if ($uploadedFile) {
                $newFileName = $this->uploaderService->getFileName($uploadedFile);
                $directoryPath = $this->getParameter('salles_directory');
                $relativePath = $this->uploaderService->getPath($newFileName, $directoryPath, $uploadedFile);
                $image = new Image();
                $image->setPath($relativePath);
                $salleExistant->setImage($image);
                $manager->persist($image);
            }
            $manager->persist($salleExistant);
            $manager->flush();
            $jsonSalle = $serializer->serialize($salleExistant, 'json',['groups'=>'salle']);
            $statusCode = $salleExistant->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate('salle_show', ['id' => $salleExistant->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            return new Response($jsonSalle, $statusCode, [$location]);
        } catch (Exception) {
            // handle any other errors
            return new JsonResponse(['message' => 'An error occurred.'], Response::HTTP_BAD_REQUEST);
        }
    }
}
