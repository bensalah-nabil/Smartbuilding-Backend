<?php

namespace App\DataFixtures;

use App\Entity\Food;
use App\Entity\Menu;
use App\Entity\RepasParJour;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RepasParJourFixture extends Fixture
{
    public function load(ObjectManager $manager):void
    {
        $faker = Factory::create();
//
//        for ($i = 0; $i < 10; $i++) {
//            $food = new Food();
//            $food->setName($faker->unique()->sentence($nbWords = 2, $variableNbWords = true));
//            $food->setCategory($faker->randomElement(['Appetizers', 'Entrees', 'Desserts', 'Beverages']));
//            $food->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 5, $max = 50));
//            $manager->persist($food);
//            for ($i = 0; $j < 10; $j++) {
//                $repasParJour = new \App\Entity\RepasParJour();
//                $repasParJour->setStock(5);
//                $repasParJour->setFood($food);
//                $manager->persist($repasParJour);
//                for ($k = 0; $k < 10; $k++) {
//                    $menu = new Menu();
//                    $menu->setDate($faker->dateTimeBetween('-1 week', '+1 week'));
//                    $menu->addRepasParJour($repasParJour);
//                    $manager->persist($menu);
//                }
//            }
//            $manager->persist($food);
//            $manager->flush();
//        }

        $food = new Food();
        $food->setName($faker->unique()->sentence($nbWords = 2, $variableNbWords = true));
        $food->setCategory($faker->randomElement(['Appetizers', 'Entrees', 'Desserts', 'Beverages']));
        $food->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 5, $max = 50));

        $menu = new Menu();
        $menu->setDate($faker->dateTimeBetween('-1 week', '+1 week'));

        $repasParJour = new RepasParJour();
        $repasParJour->setStock(5);
        $repasParJour->setFood($food);
        $repasParJour->setMenu($menu);

        $manager->persist($food);
        $manager->persist($repasParJour);
        $manager->persist($menu);

        $manager->flush();
    }
}