<?php

namespace App\DataFixtures;

use App\Entity\RepasParJour;
use App\Entity\Menu;
use App\Entity\Food;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MenuFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        // create 10 menus
        for ($i = 0; $i < 10; $i++) {
            $menu = new Menu();
            $menu->setDate($faker->dateTimeBetween('-1 week', '+1 week'));

            // create 5 foods for each menu
            for ($j = 0; $j < 5; $j++) {
                $repasParJour = new RepasParJour();
                $repasParJour->setStock  (5);
                $menu->addRepasParJour($repasParJour);
                $manager->persist($repasParJour);
                $menu->addRepasParJour($repasParJour);
            }
            $manager->persist($menu);
            $manager->flush();
        }
        $manager->flush();
    }
}