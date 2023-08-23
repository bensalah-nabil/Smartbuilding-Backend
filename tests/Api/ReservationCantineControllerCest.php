<?php

namespace App\Tests\Api;

use App\Entity\Food;
use App\Entity\Image;
use App\Entity\RepasParJour;
use App\Entity\ReservationCantine;
use App\Entity\ReservationRepas;
use App\Entity\User;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class ReservationCantineControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->faker = Factory::create();
        $this->manager = $doctrine->_getEntityManager();
    }

    public function _before(ApiTester $I)
    {
        $this->food = $this->createFood();
        $this->repasParJour = $this->createRepasParJour();
//        $this->reservationRepas = $this->createReservationRepas();
        $this->reservationCantine = $this->createReservationCantine();
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
//            ->setReservationRepas($this->reservationRepas)
            ->setFood($this->food);
        $this->manager->persist($repasParJour);
        $this->manager->flush();
        return $repasParJour;
    }
//    protected function createReservationRepas(): ReservationRepas
//    {
//        $reservationRepas = (new ReservationRepas())
//            ->setQuantity(5)
//            ->addRepasParJour($this->repasParJour);
//        $this->manager->persist($reservationRepas);
//        $this->manager->flush();
//        return $reservationRepas;
//    }
    protected function createReservationCantine(): ReservationCantine
    {
        $reservationCantine = (new ReservationCantine())
            ->setStatut("statut exepmle")
//            ->setReservationRepas($this->reservationRepas)
            ->setDateReservation( new \DateTime(2023-12-10))
            ->setDateConfirmation( new \DateTime(2023-12-10))
            ->setTotal(10);
        $this->manager->persist($reservationCantine);
        $this->manager->flush();
        return $reservationCantine;
    }
//    // tests
    public function getAllCantineReservationCantineTest(ApiTester $I):void
    {
        $I->sendGet('/reservationCantine');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
//    public function getDetailReservationsCantineTest(ApiTester $I)
//    {
//        $I->sendGET('/reservationCantine/' . $this->reservationCantine->getId());
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson([
//        "id" => $this->reservationCantine,
//        "statut" => "statut exepmle",
//        "total" => 10,
//        "dateReservation" => "2023-12-10 00:00:00",
//        "dateConfirmation" => "2023-12-10 00:00:00"
//        ]);
//    }
//    public function getRepas(ApiTester $I)
//    {
//        $I->sendGet('/reservationCantine/ '.  $this->reservationCantine->getId() . '/repas');
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(
//            [
//                [
//                'name' => 'foodname test',
//                'price' => 25.86,
//                'category' => 'foodcategory test',
//                'image' => '/uploads/test-image.jpg'
//                ]
//            ]);
//    }
//    public function createTest(ApiTester $I)
//    {
//        $jsonData = [
//
//                'id' => $this->reservationCantine->getId(),
//                'dateReservation' => $this->reservationCantine->getDateReservation(),
//                'dateConfirmation' => $this->reservationCantine->getDateConfirmation(),
//                'total' => $this->reservationCantine->getTotal(),
//                'statut' => $this->reservationCantine->getStatut()
//            ];
//        $I->sendPOST('/menus', json_encode($jsonData));
//        $I->seeResponseCodeIs(201);
//    }
//
//    public function testDelete(ApiTester $I)
//    {
//        $id = $this->createReservationCantine()->getId();
//        $I->sendDELETE("/reservationCantine/$id");
//        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
//        $deletedReservationCantine = $this->manager->getRepository(ReservationCantine::class)->find($id);
//        $I->assertNull($deletedReservationCantine);
//    }
}