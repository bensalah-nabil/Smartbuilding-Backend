<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\RepasParJour;
use App\Repository\RepasParJourRepository;
use App\Repository\FoodRepository;
use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1/menus')]
class MenuController extends AbstractController
{
    // All GET API: [ GET All The Menus, Get a Menu Date, Get a Menu collecttion of Foods ] //
    #[Route('', name: 'menus_list', methods: ['GET'])]
    public function getAllMenus(MenuRepository $menuRepository) :JsonResponse
    {
        return $this->json($menuRepository->findAll(), 200,[], ['groups' => 'menu:food']);
    }
    
    #[Route('/dates', name: 'menus_dates', methods: ['GET'])]
    public function showMenuDates(MenuRepository $menuRepository)
    {
        $dates = new ArrayCollection();
        $menuArray = $menuRepository->findAll();
        foreach ( $menuArray as $menu){
            $dates->add($menu->getDate());
        }
        return $this->json($dates, 200,[], ['groups' => 'menu:food']);
    }

    #[Route('/{date<^\d{4}-\d{2}-\d{2}$>}', name: 'menu_show_by_date', methods: ['GET'])]
    public function getMenuByDate(string $date, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuRepository->findByDate($date);
        if (!$menu) {
            throw $this->createNotFoundException('Menu not found');
        }
        return $this->json($menu,200,[], ['groups' => 'menu:food']);
    }

    #[Route('/{id<\d+>}', name: 'menu_show', methods: ['GET'])]
    public function getDetailMenu(Menu $menu): JsonResponse
    {
        return $this->json($menu,200,[], ['groups' => 'menu:food']);
    }

    #[Route('/{id<\d+>}/repas', name: 'food_menu_show', methods: ['GET'])]
    public function showMenuFood(Menu $menu, SerializerInterface $serializer): JsonResponse
    {
        $repasParJourNames = new ArrayCollection();
        foreach ($menu->getRepasParJour() as $repasParJour) {
            $repasParJourNames->add($repasParJour->getFood()->getName());
        }
        $jsonDate = $serializer->serialize($repasParJourNames, 'json', ['groups' => 'menu:food']);
        return new JsonResponse($jsonDate, Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    // All Post Api ( Add a Menu )
    /**
     * @throws \Exception
     */
    #[Route('', name: 'menus_create_update', methods: ['POST'])]
    public function createUpdateMenus(Request $request,
                                EntityManagerInterface $manager,
                                RepasParJourRepository $repasParJourRepo,
                                FoodRepository $foodRepository,
                                MenuRepository $menuRepository): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);
        if (!is_array($jsonData) || empty($jsonData)) {
            return $this->json(['message' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }
        $menus = new ArrayCollection();
        foreach ($jsonData as $menuData) {
            $menu = (!($menuData["id"]==null));
            // Menu exists in request
            if ($menu){
                $id = $menuData["id"];
                $menu = $menuRepository->find($id);
                $existantRepasParJours = $menu->getrepasParJour()->toArray();
                $updatedAvFoods = [];
                foreach ($menuData["repasParJour"] as $newRepasParJour) {
                    $repasParJour = (!($newRepasParJour["id"]==null));

                    // FOOD test
                    $foodId =$newRepasParJour["repasId"];
                        $food = $foodRepository->find($foodId);
                        if(!$food){
                            throw new \Exception('Food not found');
                        }
                    //repasParJour in menu doesn't exist
                    if (!$repasParJour ) {
                        $repasParJour = (new RepasParJour())
                                        ->setMenu($menu)
                                        ->setFood($food)
                                        ->setStock($newRepasParJour["stock"]);
                        $updatedAvFoods[] = $repasParJour;
                        $menu->addRepasParJour($repasParJour);
                        $manager->persist($repasParJour);
                    }elseif (in_array($newRepasParJour, $existantRepasParJours)) {
                        $updatedAvFoods[] = $repasParJour;
                    // if no , we add it to our menu
                    } else {
                        // If the avfood is new, add it to the menu
                        $repasParJour = $repasParJourRepo->find($newRepasParJour["id"]);
                        $repasParJour->setStock($newRepasParJour["stock"]);
                        $repasParJour->setFood($food);
                        $manager->persist($repasParJour);
                        
                        $menu->addrepasParJour($repasParJour);
                        $updatedAvFoods[] = $repasParJour;
                        $menu->addRepasParJour($repasParJour);
                    }
                }
                foreach ($existantRepasParJours as $repasParJour) {
                    if (!in_array($repasParJour, $updatedAvFoods)) {
                        $menu->removeRepasParJour($repasParJour);
                    }
                }
                $menus->add($menu);
                $manager->persist($menu);
                //new Date --> new menu
            }else{
                $menu = new Menu();
                $menu->setDate(new \DateTime($menuData["date"]));
                foreach ($menuData["repasParJour"] as $newRepasParJour) {
                    $repasParJour = (!($newRepasParJour["id"] == null));
                    // FOOD test
                    $foodId =$newRepasParJour["repasId"];
                    $food = $foodRepository->find($foodId);
                        if(!$food){
                            throw new \Exception('Food not found');
                        }
                    if (!$repasParJour) {
                        $repasParJour = (new RepasParJour())
                                        ->setMenu($menu)
                                        ->setFood($food)
                                        ->setStock($newRepasParJour["stock"]);
                        $updatedAvFoods[] = $repasParJour;

                        $manager->persist($repasParJour);
                        $menu->addRepasParJour($repasParJour);
                    }else{
                        $repasParJourId = $newRepasParJour['id'];
                        $repasParJour = $repasParJourRepo->find($newRepasParJour["id"]);
                        $menu->addRepasParJour($repasParJour);
                    }  

                }
                $menus->add($menu);
                $manager->persist($menu);
            }
        }
        $manager->flush();
        return $this->json(['menus' => $menus],Response::HTTP_CREATED ,[], ['groups'=>'menu:food']);
    }

    // Update API: [ Change a Menu Date, Change a Menu Collection of Food( Add A food - Delete a food  )] //
    // Remove foods that are in the existing menu but not in the new request
    #[Route('/{id<\d+>}', name: 'menu_delete', methods: ['DELETE'])]
    public function delete(Menu $menu,EntityManagerInterface $manager): JsonResponse
    {
        foreach ($menu->getRepasParJour() as $food) {
            $menu->removerepasParJour($food);
            $manager->persist($menu);
        }
        $manager->remove($menu);
        $manager->flush();

        return new JSONResponse('', Response::HTTP_NO_CONTENT);
    }

}