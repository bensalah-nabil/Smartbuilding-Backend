<?php

namespace App\Controller;

use App\Entity\Food;
use App\Entity\Image;
use App\Repository\FoodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\UploaderService;


    #[Route('/api/v1/foods')]
    class FoodController extends AbstractController
    {
    private UploaderService $uploaderService;
    
    public function __construct(UploaderService $uploaderService)
    {
        $this->uploaderService = $uploaderService;
    }

    #[Route('', name: 'food_list', methods: ['GET'])]
    public function getAllFoods(FoodRepository $foodRepository): JsonResponse
    {
        return $this->json($foodRepository->findAll(), 200,[],['groups'=>['food','menu:food']]);
    }

    #[Route('/{id<\d+>}', name: 'food_show', methods: ['GET'])]
    public function getDetailFoods(Food $food, SerializerInterface $serializer,FoodRepository $foodRepository): JsonResponse
    {
        $food = $foodRepository->find($food);
        if (!$food) {
            return new JsonResponse(['message' => 'Food not found.'], Response::HTTP_NOT_FOUND);
        }

        $jsonFood = $serializer->serialize($food, 'json', ['groups' => 'food']);
        return new JsonResponse($jsonFood, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ], true);
    }

    #[Route('/{id<\d+>}', name: 'food_delete', methods: ['DELETE'])]
    public function deleteFoodById(int $id, FoodRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $food = $repository->find($id);

        if (!$food) {
            return new JsonResponse(['message' => 'Food not found.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($food);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('', name: 'food_create', methods: ['POST'])]
    public function addFood(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $manager,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        try {
            // Food
            $jsonFood = json_decode($request->request->get('food'));
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data');
            }
            $name = $jsonFood->name;
            $category = $jsonFood->category;
            $price = $jsonFood->price;

            // $jsonFood2 = $serializer->deserialize($request->request->get('food'), Food::class,'json');

            $food = $manager->getRepository(Food::class)->findOneBy(['name' => $name]);
            if ($food){
                throw new \Exception(sprintf('Food with name "%s" already exists.', $name));
            }

            $food = new Food();
            $food->setName($name);
            $food->setCategory($category);
            $food->setPrice($price);

            // Image
            $uploadedFile = $request->files->get('image');

            if (!$uploadedFile) {
                throw new \Exception('No image uploaded.');
            }

            $newFileName = $this->uploaderService->getFileName($uploadedFile);

            $directoryPath = $this->getParameter('foods_directory');

            $relativePath = $this->uploaderService->getPath($newFileName,$directoryPath,$uploadedFile);


            $image = new Image();
            $image->setPath($relativePath);
            $food->setImage($image);

            $manager->persist($food);
            $manager->persist($image);
            $manager->flush();

            $jsonFood = $serializer->serialize($food, 'json');
            $statusCode = $food->getId() ? Response::HTTP_OK : Response::HTTP_CREATED;
            $location = $urlGenerator->generate('food_show', ['id' => $food->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new Response($jsonFood, $statusCode, ['Location' => $location]);
        } catch (\Exception $e) {
            $message = 'Missing required fields.';
            if ($e->getMessage() === 'Invalid JSON data') {
                $message = 'Invalid JSON data';
            }
            return new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/update', name: 'food_update', methods: ['POST'])]
    public function updateFood(
        Request $request,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        FoodRepository $foodRepository
    ): Response
    {
        try {
            // Food
            $jsonFood = json_decode($request->request->get('food'));
            if ($jsonFood === null) {
                return new JsonResponse(['message' => 'Invalid request.'], Response::HTTP_BAD_REQUEST);
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['message' => 'Invalid JSON format.'], Response::HTTP_BAD_REQUEST);
            }
            $id = $jsonFood->id ?? -1;
            $name = $jsonFood->name;
            $category = $jsonFood->category;
            $price = $jsonFood->price;
//          $food = $manager->getRepository(Food::class)->find($id);
            $food = $foodRepository->find($id);
            if (!$food) {
                return new JsonResponse(['message' => 'Food not found.'], Response::HTTP_NOT_FOUND);
            }
            $food->setName($name);
            $food->setCategory($category);
            $food->setPrice($price);

            // Image
            $uploadedFile = $request->files->get('image');
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $randomBytes = random_bytes(16);
                $randomHex = bin2hex($randomBytes);
                $extension = $uploadedFile->guessExtension();
                $newFilename = Urlizer::urlize($originalFilename) . '-' . $randomHex . '.' . $extension;
                $directoryPath = $this->getParameter('foods_directory');
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
                $food->setImage($image);
                $manager->persist($image);
            }
            $manager->persist($food);
            $manager->flush();
            // Prepare response
            $jsonFood = $serializer->serialize($food, 'json', ['groups' => 'food']);
            $statusCode = $food->getId() ? JsonResponse::HTTP_OK : JsonResponse::HTTP_CREATED;
            $location = $urlGenerator->generate(
                'food_show',
                ['id' => $food->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            return new Response($jsonFood, $statusCode, ['Location' => $location]);
        } catch (\Exception $e) {
            $message = 'Missing required fields.';
            if ($e->getMessage() === 'Invalid JSON data') {
                $message = 'Invalid JSON data';
            }
            return new JsonResponse(['message' => $message], Response::HTTP_BAD_REQUEST);
        }
    }
}

