<?php

namespace App\Tests\Api;

use App\Entity\Image;
use App\Entity\Incident;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class IncidentControllerCest
{
    private EntityManagerInterface $manager;

    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->faker = Factory::create();
        $this->manager = $doctrine->_getEntityManager();
    }
    // tests
    public function getAllIncidentsTest(ApiTester $I):void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/incident');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }

    public function deleteTest(ApiTester $I)
    {
        // create a new incident entity to delete
        $incident = new Incident();
        $date = new DateTime('2023-04-11');
        $incident->setDate($date);
        $incident->setLocalisation('Batiment 2');
        $incident->setCategorie('equipement');
        $incident->setStatut('en cours');
        $incident->setDescription('incident batiment 2');
        $incident->setDegre('moyenne');
        $incident->setPiece('Toilettes');

        $this->manager->persist($incident);
        $this->manager->flush();

        $id = $incident->getId();

        // send a DELETE request to delete the incident  entity
        $I->sendDELETE("/incident/$id");
        // check the response code
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        // check that the incident entity was deleted from the database
        $deletedIncident = $this->manager->getRepository(Incident::class)->find($id);
        $I->assertNull($deletedIncident);
    }
    public function testIncidentEntity(ApiTester $I)
    {
        $date = new DateTime('2023-04-11');
        $incident = (new Incident())
            ->setDate($date)
            ->setLocalisation('Batiment 2')
            ->setCategorie('equipement')
            ->setStatut('en cours')
            ->setDescription('incident batiment 2')
            ->setDegre('moyenne')
            ->setPiece('Toilettes');

        $this->manager->persist($incident);
        $this->manager->flush();

        $I->assertNotNull($incident->getId());
        $I->assertEquals("Batiment 2", $incident->getLocalisation());
        $I->assertEquals("equipement", $incident->getCategorie());
        $I->assertEquals("en cours", $incident->getStatut());
        $I->assertEquals("incident batiment 2", $incident->getDescription());
        $I->assertEquals("moyenne", $incident->getDegre());
        $I->assertEquals("Toilettes", $incident->getPiece());
        $I->assertNull($incident->getImage());

    }
    public function testGetDetailIncidents(ApiTester $I)
    {
        // create a new incident entity to retrieve
        $incident = new Incident();
        $date = new DateTime('2023-04-11');
        $incident->setDate($date);
        $incident->setLocalisation('Batiment 2');
        $incident->setCategorie('equipement');
        $incident->setStatut('en cours');
        $incident->setDescription('incident batiment 2');
        $incident->setDegre('moyenne');
        $incident->setPiece('Toilettes');
        $this->manager->persist($incident);
        $this->manager->flush();
        $id = $incident->getId();

        // send a GET request to retrieve the incident entity
        $I->sendGET("/incident/$id");
        // check the response code
        $I->seeResponseCodeIs(HttpCode::OK);
        // check that the response contains the expected incident data
        $expectedJson = [
            'id' => $id,
            'date' => '2023-04-11T00:00:00+02:00',
            'localisation' => 'Batiment 2',
            'categorie' => 'equipement',
            'statut' => 'en cours',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes',
            'image' => null,
        ];
        $I->seeResponseContainsJson($expectedJson);
    }
    public function addIncidentTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->haveHttpHeader('Accept', 'application/json');
        //Incident
        $incidentData = [
            'date' => '2023-04-11T00:00:00+02:00',
            'localisation' => 'Batiment 2',
            'categorie' => 'equipement',
            'statut' => 'en cours',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes',
        ];
        //Image
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
        $imageFile = new UploadedFile($tempFile,'test-image.jpg');


        //Request
        $I->sendPOST('/incident',
            ['incident' => json_encode($incidentData)],
            ['image' => $imageFile]
        );
        $I->seeResponseIsJson();
        // Assert that the response contains the incident entity data
    }
    public function updateIncidentTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->haveHttpHeader('Accept', 'application/json');

        $date = new DateTime('2023-04-11');
        $incident = (new Incident())
            ->setDate($date)
            ->setLocalisation('Batiment 2')
            ->setCategorie('equipement')
            ->setStatut('en cours')
            ->setDescription('incident batiment 2')
            ->setDegre('moyenne')
            ->setPiece('Toilettes')
            ->setImage(null);
        $this->manager->persist($incident);
        $this->manager->flush();

        // Updated incident data
        $incidentData = [
            'id' => $incident->getId(), // Replace with the ID of the incident entity you want to update
            'date' => '2023-03-11T00:00:00+02:00',
            'localisation' => 'Batiment 1',
            'categorie' => 'equipement',
            'statut' => 'Non traité',
            'description' => 'incident batiment 1',
            'degre' => 'faible',
            'piece' => 'Toilettes',
        ];

        // Updated image file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
        $imageFile = new UploadedFile($tempFile, 'test-image.jpg');

        // Send request
        $I->sendPOST('incident/update', [
            'incident' => json_encode($incidentData),
            'image' => $imageFile,
        ]);

        // Assert response
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function testGetIncidentWithImage(ApiTester $I): void
    {
        // Create a new incident entity with an image
        $date = new DateTime('2023-04-11');
        $incident = (new Incident())
            ->setDate($date)
            ->setLocalisation('Batiment 2')
            ->setCategorie('equipement')
            ->setStatut('en cours')
            ->setDescription('incident batiment 2')
            ->setDegre('moyenne')
            ->setPiece('Toilettes');
        $image = (new Image())
            ->setPath('tests/_data/test-image.jpg')
            ->setIncident($incident);
        $incident->setImage($image);
        // Persist the entities
        $this->manager->persist($incident);
        $this->manager->persist($image);
        $this->manager->flush();

        // Send a GET request to the endpoint with the ID of the created entity
        $I->sendGET('/incident/' . $incident->getId());
        // Assert that the response code is 200 OK
        $I->seeResponseCodeIs(HttpCode::OK);
        // Assert that the response is in JSON format
        $I->seeResponseIsJson();
        // Assert that the response contains the food entity data
        $I->seeResponseContainsJson([
            'date' => '2023-04-11T00:00:00+02:00',
            'localisation' => 'Batiment 2',
            'categorie' => 'equipement',
            'statut' => 'en cours',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes',
            'image' => 'tests/_data/test-image.jpg'
        ]);
    }
    public function getIncidentByIdNotFound(ApiTester $I)
    {
        $I->sendGET('/incident/9000');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
    public function testCreateIncidentFailure(ApiTester $I)
    {
        $formData = [
            'date' => new DateTime('2023-04-11'),
            'localisation' => 1,
            'categorie' => 'equipement',
            'statut' => '',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes',
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/incident', [
            'incident' => json_encode($formData),
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
    }
    public function testIncidentUpdateFailure(ApiTester $I)
    {
        // Send an invalid JSON payload to update a incident item
        $I->sendPOST('incident/update', [
            'incident' => 'invalid-json',
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'message' => 'Invalid request.'
        ]);
    }
    public function testGetAllIncidentsEmpty(ApiTester $I)
    {
        // Delete all existing incidents
        $I->sendGET('/incident');
        $incidents = json_decode($I->grabResponse(), true);
        foreach ($incidents as $incident) {
            $I->sendDELETE('/incident/' . $incident['id']);
        }

        // Request all incidents and check for empty response
        $I->sendGET('/incident');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseEquals('[]');
    }
    public function testDeleteIncidentNotFound(ApiTester $I)
    {
        $I->sendDELETE('/incident/9003');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        //$I->seeResponseContainsJson(['message' => 'Incident not found.']);
    }
    public function testCreateIncidentMissingFields(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $incident = $I->haveInRepository(Incident::class, ['date' => new DateTime('2023-04-11'),
            'localisation' => 'Batiment 2',
            'categorie' => 'equipement',
            'statut' => 'en cours',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes']);
        $I->sendPOST('/incident', [
            'incident' => json_encode(['id' => $incident, 'localisation' => 'Updated Test Incident']),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        //$I->seeResponseContainsJson(['message' => 'An error occurred.']);
    }
    public function testUpdateIncidentMissingFields(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $incident = $I->haveInRepository(Incident::class, ['date' => new DateTime('2023-04-11'),
            'localisation' => 'Batiment 2',
            'categorie' => 'equipement',
            'statut' => 'en cours',
            'description' => 'incident batiment 2',
            'degre' => 'moyenne',
            'piece' => 'Toilettes']);
        $I->sendPOST('/incident/update', [
            'incident' => json_encode(['id' => $incident, 'name' => 'Updated Test Incident']),
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['message' => 'An error occurred.']);
    }
    public function testUpdateIncidentNotFound(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $imagePath = codecept_data_dir('test-image.jpg');
        $uploadedFile = new UploadedFile($imagePath, 'test_image.jpg', 'image/jpeg', null, true);
        $I->sendPOST('/incident/update', [
            'incident' => json_encode(['id' => 9999,'date' => new DateTime('2023-04-11'),
                'localisation' => 'Batiment 2',
                'categorie' => 'equipement',
                'statut' => 'en cours',
                'description' => 'incident batiment 2',
                'degre' => 'moyenne',
                'piece' => 'Toilettes']),
            'image' => $uploadedFile
        ]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson(['message' => 'Incident not found.']);
    }
    public function testCreateFoodFailure(ApiTester $I)
    {
        $formData = [
            'name' => '',
            'category' => 'Main Course',
            'price' => -10.0,
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/foods', [
            'food' => json_encode($formData),
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'message' => 'Invalid JSON data',
        ]);
    }
}