<?php

namespace App\Tests\Api;

use App\Entity\ReservationRepas;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class ReservationRepasControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->faker = Factory::create();
        $this->manager = $doctrine->_getEntityManager();
    }
    // tests
    public function getAllReservationRepasReservationTest(ApiTester $I):void
    {
        $I->sendGet('/reservationRepas');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
//    public function getReservationRepasByIdSuccess(ApiTester $I)
//    {
//        $reservationRepas = $I->haveInRepository(ReservationRepas::class,
//            [
//                'quantity' => 5,
//            ]);
//        $I->sendGET('/reservationRepas/' . $reservationRepas);
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['quantity' => 5]);
//    }

//    public function testCreateReservationRepasSuccess(ApiTester $I)
//    {
//        $I->wantTo('Create a new ReservationRepas item');
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->wantTo('add a new ReservationRepas');
//        // reservationRepas
//        $reservationRepasData = [
//            'quantity' => null,
//        ];
//        // Request
//        $I->sendPOST('/api/v1/reservationRepas', [
//            'reservationRepas' => $reservationRepasData,
//        ]);
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseMatchesJsonType([
//            'id' => 'integer',
//            'quantity' => 'integer',
//        ]);
//    }


//    public function testGetDetailReservationRepas(ApiTester $I)
//    {
//        // create a new reservationRepas entity to retrieve
//        $reservationRepas = new ReservationRepas();
//        $reservationRepas->setQuantity(3);
//        $this->manager->persist($reservationRepas);
//        $this->manager->flush();
//        $id = $reservationRepas->getId();
//
//        // send a GET request to retrieve the reservationRepas entity
//        $I->sendGET("/reservationRepas/$id");
//        // check the response code
//        $I->seeResponseCodeIs(HttpCode::OK);
//        // check that the response contains the expected reservationRepas data
//        $expectedJson = [
//            'id' => $id,
//            'quantity' => 3,
//        ];
//        $I->seeResponseContainsJson($expectedJson);
//    }

//    public function testCreateReservationRepasFailure(ApiTester $I)
//    {
//        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
//        $I->haveHttpHeader('Accept', 'application/json');
//        //ReservationRepas
//        $reservationRepasData = [
//            'quantity' => '3',
//        ];
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->sendPOST('/reservationRepas', [
//            'reservationRepas' => json_encode($reservationRepasData),
//        ]);
//
//        $I->seeResponseCodeIs(400);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson([
//            'message' => 'Invalid JSON data',
//        ]);
//    }

//    public function getReservationRepasByIdSuccess(ApiTester $I)
//    {
//        // Create a reservation in the database
//        $reservationRepas = $I->haveInRepository(ReservationRepas::class,
//            [
//                'quantity' => 4,
//            ]);
//
//        // Fetch the reservation object from the repository using the ID
//        $reservation = $I->grabEntityFromRepository(ReservationRepas::class, ['id' => $reservationRepas]);
//
//        // Make the GET request to the endpoint using the ID
//        $I->sendGET('/reservationRepas/' . $reservation->getId());
//
//        // Check the response
//        $I->seeResponseCodeIs(HttpCode::OK);
//        $I->seeResponseIsJson();
//        $I->seeResponseContainsJson(['id'=>$reservation.getId(),'quantity' => 4]);
//    }
    public function getReservationRepasByIdNotFound(ApiTester $I)
    {
        $I->sendGET('/reservationRepas/9000');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
    public function deleteTest(ApiTester $I)
    {
        // create a new Reservation Repas entity to delete
        $reservationRepas = new ReservationRepas();
        $reservationRepas->setQuantity(4);

        $this->manager->persist($reservationRepas);
        $this->manager->flush();

        $id = $reservationRepas->getId();

        // send a DELETE request to delete the reservationRepas  entity
        $I->sendDELETE("/reservationRepas/$id");
        // check the response code
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        // check that the ReservationRepas entity was deleted from the database
        $deletedReservationRepas = $this->manager->getRepository(ReservationRepas::class)->find($id);
        $I->assertNull($deletedReservationRepas);
    }
    public function testReservationRepasEntity(ApiTester $I)
    {
        $reservationRepas = (new ReservationRepas())
            ->setQuantity(3);

        $this->manager->persist($reservationRepas);
        $this->manager->flush();

        $I->assertNotNull($reservationRepas->getId());
        $I->assertEquals("3", $reservationRepas->getQuantity());

    }
    public function addReservationRepasTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->haveHttpHeader('Accept', 'application/json');
        //ReservationRepas
        $reservationRepasData = [
            'quantity' => 3,
        ];

        //Request
        $I->sendPOST('/reservationRepas',
            ['reservationRepas' => json_encode($reservationRepasData)],
        );
        $I->seeResponseIsJson();
        // Assert that the response contains the incident entity data
    }
    public function testGetAllReservationRepasEmpty(ApiTester $I)
    {
        // Delete all existing reservations
        $I->sendGET('/reservationRepas');
        $reservations = json_decode($I->grabResponse(), true);
        foreach ($reservations as $reservation) {
            $I->sendDELETE('/reservationRepas/' . $reservation['id']);
        }

        // Request all reservationRepas and check for empty response
        $I->sendGET('/reservationRepas');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseEquals('[]');
    }
    public function testDeleteReservationRepasNotFound(ApiTester $I)
    {
        $I->sendDELETE('/reservationRepas/9000');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }





}