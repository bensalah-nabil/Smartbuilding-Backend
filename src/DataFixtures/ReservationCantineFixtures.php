<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\ReservationCantine;
use App\Entity\Food;
use App\Entity\Menu;
use App\Entity\RepasParJour;
use  App\Entity\ReservationRepas;
use Faker\Factory;


class ReservationCantineFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {


            $food = new Food();
            $food->setName($faker->unique()->sentence($nbWords = 2, $variableNbWords = true));
            $food->setCategory($faker->randomElement(['Appetizers', 'Entrees', 'Desserts', 'Beverages']));
            $food->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 5, $max = 50));
            $manager->persist($food);

            $menu = new Menu();
            $menu->setDate($faker->dateTimeBetween('-1 week', '+1 week'));
            $manager->persist($menu);

            $repasParJour = new RepasParJour();
            $repasParJour->setStock(5);
            $repasParJour->setFood($food);
            $repasParJour->setMenu($menu);
            $manager->persist($repasParJour);

            $reservationRepas = new ReservationRepas();
            $reservationRepas->setQuantity($faker->randomFloat($nbMaxDecimals = 2, $min = 5, $max = 50));
            $reservationRepas->addRepasParJour($repasParJour);

            $reservation = new ReservationCantine();
            $reservation->setTotal(500);
            $reservation->setStatut('confirmed');
            $reservation->setTotal(2);
            $reservation->setDateReservation(new \DateTime());
            $reservation->setDateConfirmation(new \DateTime());
            
            $reservationRepas->addReservationCantine($reservation);
            
            $manager->persist($reservation);


        }

        $manager->flush();
    }
}
