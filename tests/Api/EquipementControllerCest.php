<?php

namespace App\Tests\Api;

use App\Tests\Support\ApiTester;
use Codeception\Module\Doctrine2;
use Codeception\Util\HttpCode;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;

class EquipementControllerCest
{

    private EntityManagerInterface $manager;
    protected function _inject(Doctrine2 $doctrine): void
    {
        $this->faker = Factory::create();
        $this->manager = $doctrine->_getEntityManager();
    }
    // tests
    public function getAllEquipementsTest(ApiTester $I):void
    {
        $I->sendGet('/equipements');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
    }
}