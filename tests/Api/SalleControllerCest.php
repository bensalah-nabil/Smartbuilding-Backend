<?php

namespace App\Tests\Api;

use App\Entity\Image;
use App\Entity\SalleReunion;
use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SalleControllerCest
{
    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->manager = $doctrine->_getEntityManager();
    }
    // tests
    public function getAllRoomsTest(ApiTester $I):void
    {
        $I->sendGet('/salles');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function _before(ApiTester $I)
    {
        $this->salle = $this->createSalle();
    }
    protected function createSalle(): SalleReunion
    {
        $image = (new Image())
            ->setPath('/uploads/test-image.jpg');
        $salle = (new SalleReunion())
            ->setNom("name test")
            ->setEmplacement("emplacement test")
            ->setStatut("statut test")
            ->setCapacite(5)
            ->setImage($image);
        $this->manager->persist($image);
        $this->manager->persist($salle);
        $this->manager->flush();
        return $salle;
    }
    // tests
    public function getAllSallesTest(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGet('/salles');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
    public function getSalleByIdTest(ApiTester $I): void
    {
        $I->sendGET('/salles/' . $this->salle->getId());
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "id" => $this->salle->getId(),
            "nom" => "name test",
            "emplacement" => "emplacement test",
            "capacite" => 5,
            "statut" => "statut test",
            "image" => "/uploads/test-image.jpg"
        ]);
        // Retrieve the salle object from the database
        $retrievedSalle = $this->manager->getRepository(SalleReunion::class)->find($this->salle);
        // Check if the retrieved salle object matches the original salle object
        $I->assertEquals($this->salle->getNom(), $retrievedSalle->getNom());
    }

    public function deleteSalle(ApiTester $I)
    {
        $id = $this->salle->getId();
        $I->sendDELETE("/salles/$id");
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $deletedSalle = $this->manager->getRepository(SalleReunion::class)->find($id);
        $I->assertNull($deletedSalle);
    }
    public function addSalleTest(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->haveHttpHeader('Accept', 'application/json');
        //Salle
        $salleData = [
            "id" => 8,
            "nom" => "name test",
            "emplacement" => "emplacement test",
            "capacite" => 5,
            "statut" => "statut test",
            "image" => "/uploads/test-image.jpg"
        ];
        //Image
        $tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
        $imageFile = new UploadedFile($tempFile, 'test-image.jpg');
        //Request
        $I->sendPOST('/salles',
            ['salle' => json_encode($salleData)],
            ['image' => $imageFile]
        );
        $I->seeResponseIsJson();
        // Assert that the response contains the salle entity data
    }
    public function updateSalle(ApiTester $I)
    {
        $I->wantTo('Update an existing salle');
        $salleId = $this->salle->getId();
        $I->haveHttpHeader('Content-Type', 'multipart/form-data');
        $I->sendPOST('/salles/update', [
            'salle' => json_encode([
                'id' => $salleId,
                "nom" => "name test",
                "emplacement" => "emplacement test",
                "capacite" => 5,
                "statut" => "statut test",
            ]),
            'image' => codecept_data_dir('test-image-update.jpg') // Make sure to have a test image in the _data directory
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "id" => $salleId,
            "nom" => "name test",
            "emplacement" => "emplacement test",
            "capacite" => 5,
            "statut" => "statut test",
        ]);
    }
}