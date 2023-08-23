<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Repository\EquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/v1/equipements')]
class EquipementController extends AbstractFOSRestController
{

    // Get list of suppliers
    #[OA\Get(
        description: "This is an example how to get an list of equipments",
        summary: "Get list of equipments",
        tags: ['Equipements'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'It returns list of equipments',
                content: new OA\JsonContent(ref: "#/components/schemas/Get_Equipments")
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'There is no equipments records'
            ),
        ]
    )]
    #[Route('', name: 'equipement_list', methods: ['GET'])]
    public function getAllEquipements(EquipementRepository $equipementRepository):Response
    {
        $equipements = $equipementRepository->findAll();
        $view = $this->view($equipements, Response::HTTP_OK);
        $view->getContext()->setGroups(['equipement','salle','reservations:salle']);
        return $this->handleView($view);
    }
    #[Route('/{id<\d+>}', name: 'equipement_show', methods: ['GET'])]
    public function getDetailEquipements(Equipement $equipement, SerializerInterface $serializer)
    : JsonResponse
    {
        $jsonEquipement = $serializer->serialize($equipement,'json', ['groups' => ['equipement']]);
        return new JsonResponse($jsonEquipement, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route("/{id<\d+>}/salles", name:"salles", methods: ['GET'])]
    public function getSalles($id, EquipementRepository $equipementRepository): JsonResponse
    {
        try {
            $equipement = $equipementRepository->find($id);
            if (!$equipement) {
                return $this->json(['message' => 'Equipement with id ' . $id . ' does not exist in the database'], 404);
            }
            return $this->json($equipement->getSalleReunions(),200,[],['groups' => 'salle']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'An error occurred while retrieving the equipments for salle with id ' . $id], 500);
        }
    }
    #[Route('', name: 'equipement_create', methods: ['POST'])]
    public function addEquipement(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        EquipementRepository $equipementRepository,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        try {
            $equipement = $serializer->deserialize($request->getContent(),Equipement::class,'json');
            $equipementTest = $equipementRepository->findBy(['nom' => $equipement->getNom()]);
            if($equipementTest){
                throw $this->createNotFoundException('Cet equipement exist deja' );
            }
            $manager->persist($equipement);
            $manager->flush();
            $jsonEquipement = $serializer->serialize($equipement, 'json',['groups'=>['equipement','salle']]);
            $statusCode = $equipement->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate(
                'equipement_show',
                ['id' => $equipement->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            return new Response($jsonEquipement, $statusCode, [
                'Location' => $location
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id<\d+>}', name: 'equipement_delete', methods: ['DELETE'])]
    public function delete(Equipement $equipement,
                           EntityManagerInterface $manager)
    : JsonResponse
    {
        $manager->remove($equipement);
        $manager->flush();
        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }
}
