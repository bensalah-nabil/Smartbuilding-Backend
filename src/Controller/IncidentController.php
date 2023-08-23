<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Incident;
use App\Entity\User;
use App\Repository\IncidentRepository;
use App\Service\NotificationService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/incident')]
class IncidentController extends AbstractController
{
    #[Route('', name: 'incident_list', methods: ['GET'])]
    public function getAllIncidents(IncidentRepository $incidentRepository,
                                       SerializerInterface $serializer)
    : JsonResponse
    {
        $incidents = $incidentRepository->findAll();
        $jsonIncidentsList = $serializer->serialize($incidents, 'json', ['groups' => ['incident','incidents:user']]);

        return new JsonResponse($jsonIncidentsList,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json',
                'Access-Control-Allow-Origin', '*'],

            true);

    }

    #[Route('/{id<\d+>}', name: 'incident_show', methods: ['GET'])]
    public function getDetailIncidents(Incident $incident, SerializerInterface $serializer)
    : JsonResponse
    {
        $jsonIncident = $serializer->serialize($incident,'json', ['groups' => ['incident','incidents:user']]);
        return new JsonResponse($jsonIncident, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    #[Route('/user/{id<\d+>}', name: 'incident_show', methods: ['GET'])]
    public function getIncidentsByUserId(User $user, SerializerInterface $serializer)
    : JsonResponse
    {
        $incidents = $user->getIncidents();
        $serializedIncidents = $serializer->serialize($incidents, 'json', ['groups' => ['incident','incidents:user']]);
        return new JsonResponse($serializedIncidents, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true,
        );
    }

    #[Route('', name: 'incident_create', methods: ['POST'])]
    public function addIncident(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator,
        NotificationService $notificationService,
    ): Response {
        try {
            $incident = $serializer->deserialize($request->request->get('incident'), Incident::class,'json');
            $incident->setUser($this->getUser());

            // Image
            $uploadedFile = $request->files->get('image');
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

                $randomBytes = random_bytes(16);
                $randomHex = bin2hex($randomBytes);
                $extension = $uploadedFile->guessExtension();
                $newFilename = Urlizer::urlize($originalFilename) . '-' . $randomHex . '.' . $extension;
                $directoryPath = $this->getParameter('incidents_directory');
                if (!is_dir($directoryPath)) {
                    throw new \Exception(sprintf('The directory "%s" does not exist.', $directoryPath));
                }
                $newPath = $directoryPath . '/' . $newFilename;

                if (!move_uploaded_file($uploadedFile->getPathname(), $newPath)) {
                    throw new \Exception(sprintf('An error occurred while uploading the file "%s".', $uploadedFile->getClientOriginalName()));
                }

                $image = new Image();
                $rootDir = $this->getParameter('kernel.project_dir');
                $relativePath = str_replace($rootDir . '/public', '', $newPath);

                $image->setPath($relativePath);
                $incident->setImage($image);
                $manager->persist($image);
            }

            $manager->persist($incident);
            $manager->flush();
            $jsonIncident = $serializer->serialize($incident, 'json',['groups' => ['incident','incidents:user']]);

            $statusCode = $incident->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate(
                'incident_show',
                ['id' => $incident->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $notificationService->createNotificationforResponsableMaintenance('Incident','Un nouvel Incident a été déclaré',$incident->getId());
            return new Response($jsonIncident, $statusCode, [
                'Location' => $location
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/update', name: 'incident_update', methods: ['POST'])]
    public function updateIncident(
        Request $request,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        NotificationService $notificationService,
    ): Response {
        try {
            // Incident
            $jsonIncident = json_decode($request->request->get('incident'));
            if ($jsonIncident === null) {
                return new JsonResponse(['message' => 'Invalid request.'], Response::HTTP_BAD_REQUEST);
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['message' => 'Invalid JSON format.'], Response::HTTP_BAD_REQUEST);
            }

            $id = $jsonIncident->id ?? -1;
            $date = $jsonIncident->date;
            $localisation = $jsonIncident->localisation;
            $categorie = $jsonIncident->categorie;
            $statut = $jsonIncident->statut;
            $description = $jsonIncident->description;
            $degre = $jsonIncident->degre;
            $piece = $jsonIncident->piece;


            $incident = $manager->getRepository(Incident::class)->find($id);
            if (!$incident) {
                return new JsonResponse(['message' => 'Incident not found.'], Response::HTTP_NOT_FOUND);
            }
            //to check and send notification
            $initialState = $incident->getStatut();

            $incident->setDate(new \DateTime($date));
            $incident->setLocalisation($localisation);
            $incident->setCategorie($categorie);
            $incident->setStatut($statut);
            $incident->setDescription($description);
            $incident->setDegre($degre);
            $incident->setPiece($piece);

            // Image
            $uploadedFile = $request->files->get('image');
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $randomBytes = random_bytes(16);
                $randomHex = bin2hex($randomBytes);
                $extension = $uploadedFile->guessExtension();
                $newFilename = Urlizer::urlize($originalFilename) . '-' . $randomHex . '.' . $extension;
                $directoryPath = $this->getParameter('incidents_directory');
                if (!is_dir($directoryPath)) {
                    throw new \Exception(sprintf('The directory "%s" does not exist.', $directoryPath));
                }
                $newPath = $directoryPath . '/' . $newFilename;

                if (!move_uploaded_file($uploadedFile->getPathname(), $newPath)) {
                    throw new \Exception(sprintf('An error occurred while uploading the file "%s".', $uploadedFile->getClientOriginalName()));
                }
                $rootDir = $this->getParameter('kernel.project_dir');
                $relativePath = str_replace($rootDir . '/public', '', $newPath);
                $image = new Image();
                $image->setPath($relativePath);
                $incident->setImage($image);
                $manager->persist($image);
            }
            $manager->persist($incident);
            $manager->flush();
            if($initialState !== $statut){
                $notificationService->createIncidentNotification($incident->getUser()->getId(),
                                                            'Mis à jour de l"Incident',
                                                        "L'incident que vous avez déclaré est mis à jour",
                                                                $id);
            }
            // Preparing the Response
            $jsonIncident = $serializer->serialize($incident, 'json',['groups'=>'incidents']);
            $statusCode = $incident->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate(
                'incident_show',
                ['id' => $incident->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            return new Response($jsonIncident, $statusCode, [
                'Location' => $location
            ]);
        } catch (FileException $e) {
            // handle file-related errors
            return new JsonResponse(['message' => 'An error occurred while uploading the file.'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // handle any other errors
            return new JsonResponse(['message' => 'An error occurred.'], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/{id<\d+>}', name: 'incident_delete', methods: ['DELETE'])]
    public function delete(Incident $incident,
                           EntityManagerInterface $manager)
    : JsonResponse
    {
        $manager->remove($incident);
        $manager->flush();

        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }
}

