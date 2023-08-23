<?php

namespace App\DataFixtures;

use App\Entity\ReservationCantine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class ReservationFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $reservationCantine = new ReservationCantine();
        $reservationCantine->setStatut('pending');
        $reservationCantine->setTotal(10);
        $reservationCantine->setDateReservation(new \DateTime());

        $manager->persist($reservationCantine);
        $manager->flush();
    }
}
