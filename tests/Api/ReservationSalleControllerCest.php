<?php

namespace App\Tests\Api;

use App\Entity\ReservationSalle;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use DateTime;

class ReservationSalleControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->faker = Factory::create();
        $this->manager = $doctrine->_getEntityManager();
    }
    // tests
    public function getAllRoomReservationTest(ApiTester $I):void
    {
        $I->sendGet('/reservationSalle');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function testGetDetailsReservationSalle(ApiTester $I)
    {
        // create a new Reservation Salle entity to retrieve
        $reservationSalle = new ReservationSalle();
        $dateReservation = new DateTime('2023-04-11');
        $dateConfirmation = new DateTime('2023-04-11');
        $dateDebut = new DateTime('2023-04-11');
        $dateFin = new DateTime('2023-04-11');
        $reservationSalle->setDateReservation($dateReservation);
        $reservationSalle->setDateConfirmation($dateConfirmation);
        $reservationSalle->setDateDebut($dateDebut);
        $reservationSalle->setDateFin($dateFin);
        $reservationSalle->setStatut('Disponible');
        $this->manager->persist($reservationSalle);
        $this->manager->flush();
        $id = $reservationSalle->getId();

        // send a GET request to retrieve the reservation salle entity
        $I->sendGET("/reservationSalle/$id");
        // check the response code
        $I->seeResponseCodeIs(HttpCode::OK);
        // check that the response contains the expected reservation salle data
        $expectedJson = [
            'id' => $id,
            'dateReservation' => '2023-04-11 00:00:00',
            'dateConfirmation' => '2023-04-11 00:00:00',
            'dateDebut' => '2023-04-11 00:00:00',
            'dateFin' => '2023-04-11 00:00:00',
            'statut' => 'Disponible',
        ];
        $I->seeResponseContainsJson($expectedJson);
    }
    public function addReservationSalleTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->haveHttpHeader('Accept', 'application/json');
        //ReservationSalle
        $reservationSalleData = [
            'dateReservation' => '2023-04-11 00:00:00',
            'dateConfirmation' => '2023-04-11 00:00:00',
            'dateDebut' => '2023-04-11 00:00:00',
            'dateFin' => '2023-04-11 00:00:00',
            'statut' => 'Disponible',
        ];


        //Request
        $I->sendPOST('/reservationSalle',
            ['reservationSalle' => json_encode($reservationSalleData)]
        );
        $I->seeResponseIsJson();
        // Assert that the response contains the reservationSalle entity data
    }
    public function deleteTest(ApiTester $I)
    {
        // create a new reservation salle entity to delete
        $reservationSalle = new ReservationSalle();
        $dateReservation = new DateTime('2023-04-11');
        $dateConfirmation = new DateTime('2023-04-11');
        $dateDebut = new DateTime('2023-04-11');
        $dateFin = new DateTime('2023-04-11');
        $reservationSalle->setDateReservation($dateReservation);
        $reservationSalle->setDateConfirmation($dateConfirmation);
        $reservationSalle->setDateDebut($dateDebut);
        $reservationSalle->setDateFin($dateFin);
        $reservationSalle->setStatut('Disponible');

        $this->manager->persist($reservationSalle);
        $this->manager->flush();

        $id = $reservationSalle->getId();

        // send a DELETE request to delete the reservation salle  entity
        $I->sendDELETE("/reservationSalle/$id");
        // check the response code
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        // check that the reservation salle entity was deleted from the database
        $deletedReservationSalle = $this->manager->getRepository(ReservationSalle::class)->find($id);
        $I->assertNull($deletedReservationSalle);
    }
    public function testReservationSalleEntity(ApiTester $I)
    {
        $dateReservation = new DateTime('2023-04-11');
        $dateConfirmation = new DateTime('2023-04-11');
        $dateDebut = new DateTime('2023-04-11');
        $dateFin = new DateTime('2023-04-11');
        $reservationSalle = (new ReservationSalle())
            ->setDateReservation($dateReservation)
            ->setDateConfirmation($dateConfirmation)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin)
            ->setStatut('Disponible');

        $this->manager->persist($reservationSalle);
        $this->manager->flush();

        $I->assertNotNull($reservationSalle->getId());
        $I->assertEquals("2023-04-11 00:00:00", $reservationSalle->getDateReservation());
        $I->assertEquals("2023-04-11 00:00:00", $reservationSalle->getDateConfirmation());
        $I->assertEquals("2023-04-11 00:00:00", $reservationSalle->getDateDebut());
        $I->assertEquals("2023-04-11 00:00:00", $reservationSalle->getDateFin());
        $I->assertEquals("Disponible", $reservationSalle->getStatut());

    }
    public function updateReservationSalleTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');

        $dateReservation = new DateTime('2023-04-11');
        $dateConfirmation = new DateTime('2023-04-11');
        $dateDebut = new DateTime('2023-04-11');
        $dateFin = new DateTime('2023-04-11');
        $reservationSalle = (new ReservationSalle())
            ->setDateReservation($dateReservation)
            ->setDateConfirmation($dateConfirmation)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin)
            ->setStatut('Disponible');
        $this->manager->persist($reservationSalle);
        $this->manager->flush();

        // Updated reservation salle data
        $reservationSalleData = [
            'id' => $reservationSalle->getId(), // Replace with the ID of the reservation salle entity you want to update
            'dateReservation' => '2023-04-11 00:00:00',
            'dateConfirmation' => '2023-04-11 00:00:00',
            'dateDebut' => '2023-04-11 00:00:00',
            'dateFin' => '2023-04-11 00:00:00',
            'statut' => 'Disponible',
        ];

        // Send request
        $I->sendPut("/reservationSalle/" . $reservationSalle->getId(), [
            'reservationSalle' => $reservationSalleData
        ]);

        // Assert response
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
    }
    public function getResrvationSalleByIdNotFound(ApiTester $I)
    {
        $I->sendGET('/reservationSalle/9000');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
//    public function testCreateReservationSalleFailure(ApiTester $I)
//    {
//        $formData = [
//            'dateReservation' => '2023-04-11',
//            'dateConfirmation' => '2023-04-11',
//            'dateDebut' => '2023-04-11',
//            'dateFin' => '2023-04-11',
//            'statut' => '1', // Assuming 'statut' expects a string value
//        ];
//
//        $I->haveHttpHeader('Content-Type', 'application/json');
//        $I->sendPOST('/reservationSalle', [
//            'reservationSalle' => $formData,
//        ]);
//
//        $I->seeResponseCodeIs(400);
//        $I->seeResponseIsJson();
//    }
    public function testUpdateReservationSalleFailure(ApiTester $I)
    {
        // Create a new Reservation Salle entity to update
        $reservationSalle = new ReservationSalle();
        $dateReservation = new DateTime('2023-04-11');
        $dateConfirmation = new DateTime('2023-04-11');
        $dateDebut = new DateTime('2023-04-11');
        $dateFin = new DateTime('2023-04-11');
        $reservationSalle->setDateReservation($dateReservation);
        $reservationSalle->setDateConfirmation($dateConfirmation);
        $reservationSalle->setDateDebut($dateDebut);
        $reservationSalle->setDateFin($dateFin);
        $reservationSalle->setStatut('Disponible');

        $this->manager->persist($reservationSalle);
        $this->manager->flush();

        $id = $reservationSalle->getId();

        // Send a PUT request to update the reservation salle entity with invalid data
        $I->sendPUT("/reservationSalle/$id", ['reservationSalle' => json_encode([
            'dateReservation' => 'invalid-date',
            'dateConfirmation' => 'invalid-date',
            'dateDebut' => 'invalid-date',
            'dateFin' => 'invalid-date',
            'statut' => 'Invalid Statut',
        ])]);

        // Check the response code
//        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
//        // Check that the response contains the expected error message
//        $expectedError = [
//            'message' => 'Validation Failed',
//            'errors' => [
//                'dateReservation' => 'This value is not a valid date.',
//                'dateConfirmation' => 'This value is not a valid date.',
//                'dateDebut' => 'This value is not a valid date.',
//                'dateFin' => 'This value is not a valid date.',
//                'statut' => 'This value is not valid.',
//            ],
//        ];
//        $I->seeResponseContainsJson($expectedError);

        // Send a PUT request to update a non-existent reservation salle entity
        $nonExistentId = $id + 1;
        $I->sendPUT("/reservationSalle/$nonExistentId", ['reservationSalle' => json_encode([
            'dateReservation' => '2023-04-12',
            'dateConfirmation' => '2023-04-12',
            'dateDebut' => '2023-04-12',
            'dateFin' => '2023-04-12',
            'statut' => 'Disponible',
        ])]);

        // Check the response code
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
    public function testGetAllReservationSalleEmpty(ApiTester $I)
    {
        // Delete all existing reservations
        $I->sendGET('/reservationSalle');
        $reservations = json_decode($I->grabResponse(), true);
        foreach ($reservations as $reservation) {
            $I->sendDELETE('/reservationSalle/' . $reservation['id']);
        }

        // Request all reservations and check for empty response
        $I->sendGET('/reservationSalle');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseEquals('[]');
    }
    public function testDeleteReservationSalleNotFound(ApiTester $I)
    {
        $I->sendDELETE('/reservationSalle/9003');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        //$I->seeResponseContainsJson(['message' => 'Reservation salle not found.']);
    }
//    public function testCreateReservationSalleMissingFields(ApiTester $I)
//    {
//        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
//        $reservation = $I->haveInRepository(ReservationSalle::class, [
//            'dateReservation' => new DateTime('2023-04-12')]);
//        $I->sendPOST('/reservationSalle', [
//            'reservation' => json_encode(['id' => $reservation, 'dateReservation' => new DateTime('2023-03-12')]),
//        ]);
//        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
//        //$I->seeResponseContainsJson(['message' => 'An error occurred.']);
//    }

}