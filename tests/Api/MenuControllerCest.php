<?php

namespace App\Tests\Api;

use App\Entity\Food;
use App\Entity\Image;
use App\Entity\Menu;
use App\Entity\RepasParJour;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class MenuControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->manager = $doctrine->_getEntityManager();
    }
    public function _before(ApiTester $I)
    {
        $this->food = $this->createFood();
        $this->repasParJour = $this->createRepasParJour();
        $this->menu = $this->createMenu();
    }
    protected function createFood(): Food
    {
        $image = (new Image())
            ->setPath('/uploads/test-image.jpg');
        $food = (new Food())
            ->setName("foodname test")
            ->setPrice(25.86)
            ->setCategory("foodcategory test")
            ->setImage($image);
        $this->manager->persist($image);
        $this->manager->persist($food);
        $this->manager->flush();
        return $food;
    }
    protected function createRepasParJour(): RepasParJour
    {
        $repasParJour = (new RepasParJour())
            ->setStock(5)
            ->setFood($this->food);
        $this->manager->persist($repasParJour);
        $this->manager->flush();
        return $repasParJour;
    }
    protected function createMenu(): Menu
    {
        $menu = (new Menu())
            ->setDate(new \DateTime('2023-05-10'))
            ->addRepasParJour($this->repasParJour);
        $this->manager->persist($menu);
        $this->manager->flush();
        return $menu;
    }
    // tests
    public function testGetAllMenus(ApiTester $I): void
    {
        $I->sendGet('/menus');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
        public function testShowMenuDates(ApiTester $I): void
    {
        $I->sendGET('/menus/dates');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

    }
    public function testShowMenuByDate(ApiTester $I): void
    {
        $I->sendGET('/menus/' . $this->menu->getDate());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson([
//            'id' => $this->menu->getId(),
//            'date' => $this->menu->getDate(),
//            'repasParJour' => [
//                [
//                    'id' => $this->repasParJour->getId(),
//                    'stock' => $this->repasParJour->getStock(),
////                    'reservationRepas' => null,
//                    'repasId' => $this->food->getId()
//                ]
//            ]
//        ]);
//        $retrievedMenu = $this->manager->getRepository(Menu::class)->find($this->menu);
//        $I->assertEquals($this->menu->getDate(), $retrievedMenu->getDate());
        $I->seeResponseCodeIs(HttpCode::OK);
    }
    public function testGetDetDetailMenu(ApiTester $I): void
    {
        $I->sendGET('/menus/' . $this->menu->getId());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->menu->getId(),
            'date' => $this->menu->getDate(),
            'repasParJour' => [
                [
                    'id' => $this->repasParJour->getId(),
                    'stock' => $this->repasParJour->getStock(),
                    'reservationRepas' => null,
                    'repasId' => $this->food->getId()
                ]
            ]
        ]);
        $retrievedMenu = $this->manager->getRepository(Menu::class)->find($this->menu);
        $I->assertEquals($this->menu->getDate(), $retrievedMenu->getDate());
    }
    public function testShowMenuFoods(ApiTester $I): void
    {
        $I->sendGET('/menus/'. $this->menu->getId().'/repas');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function testCreateUpdateMenus(ApiTester $I)
    {
        $jsonData = [
            [
                'id' => $this->menu->getId(),
                'date' => $this->menu->getDate(),
                'repasParJour' => [
                    [
                        'id' => $this->repasParJour->getId(),
                        'repasId' => $this->food->getId(),
                        'stock' => 10,
                    ]
                ]
            ]
        ];
        $I->sendPOST('/menus', json_encode($jsonData));
        $I->seeResponseCodeIs(201);
    }
    public function testDelete(ApiTester $I)
    {
        $id = $this->menu->getId();
        $I->sendDELETE("/menus/$id");
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $deletedMenu = $this->manager->getRepository(Menu::class)->find($id);
        $I->assertNull($deletedMenu);
    }
}