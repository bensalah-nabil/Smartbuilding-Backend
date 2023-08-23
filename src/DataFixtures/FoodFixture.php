<?php

namespace App\DataFixtures;

use App\Entity\Food;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FoodFixture extends Fixture
{
    public function load(ObjectManager $manager):void
    {
        $food1 = new Food();
        $food1->setName('Pizza');
        $food1->setCategory('Italian');
        $food1->setPrice(10.0);
        $image1 = new Image();
        $image1->setPath("/uploads/foods/exemple1.jpg");
        $food1->setImage($image1);
        // Set other properties

        $food2 = new Food();
        $food2->setName('Burger');
        $food2->setCategory('American');
        $food2->setPrice(8.0);
        $image2 = new Image();
        $image2->setPath("/uploads/foods/exemple2.jpg");
        $food2->setImage($image2);
        // Set other properties

        $manager->persist($food1);
        $manager->persist($food2);
        $manager->flush();

//        $faker = Factory::create();
//
//        for ($i = 0; $i < 10; $i++) {
//            $food = new Food();
//            $food->setName($faker->unique()->sentence($nbWords = 2, $variableNbWords = true));
//            $food->setCategory($faker->randomElement(['Appetizers', 'Entrees', 'Desserts', 'Beverages']));
//            $food->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 5, $max = 50));
//
//            for ($j = 0; $j < 10; $j++) {
//                $repasParJour = new \App\Entity\RepasParJour();
//                $repasParJour->setStock(5);
//                $repasParJour->setFood($food);
//                $manager->persist($repasParJour);
//                $food->addRepasParJour($repasParJour);
//            }
//            $manager->persist($food);
//        }
//        $manager->flush();
    }
}