<?php

namespace App\Tests\Api;

use App\Entity\Food;
use App\Entity\Image;
use App\Entity\RepasParJour;
use App\Entity\ReservationRepas;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class RepasParJourControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->manager = $doctrine->_getEntityManager();
    }
    public function _before(ApiTester $I)
    {
        $this->food = $this->createFood();
        $this->reservation = $this->createReservationRepas();
        $this->repasParJour = $this->createRepasParJour();
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
    protected function createReservationRepas(): ReservationRepas
    {
        $reservation = (new ReservationRepas())
            ->setQuantity(5);
        $this->manager->persist($reservation);
        return $reservation;
    }
    protected function createRepasParJour(): RepasParJour
    {
        $RPJ = (new RepasParJour())
            ->setStock(5)
            ->setReservationRepas($this->reservation)
            ->setFood($this->food);
        $this->manager->persist($RPJ);
        $this->manager->flush();
        return $RPJ;
    }
    // tests
    public function getAllAvailableFoodTest(ApiTester $I):void
    {
        $I->sendGet('/repasParJour');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function showTest(ApiTester $I):void
    {
        $I->sendGET('/repasParJour/' . $this->repasParJour->getId());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'id' => $this->repasParJour->getId(),
            'stock' => 5,
            'repasId' => $this->food->getId(),
            'reservationRepasId' => $this->reservation->getId()
        ]);
        // Retrieve the Food object from the database
//        $retrievedFood = $this->manager->getRepository(RepasParJour::class)->find($this->repasParJour);
//        // Check if the retrieved Food object matches the original Food object
//        $I->assertEquals($this->food->getName(), $retrievedFood->getName());
//        $I->assertEquals($this->food->getCategory(), $retrievedFood->getCategory());
//        $I->assertEquals($this->food->getPrice(), $retrievedFood->getPrice());

    }
}