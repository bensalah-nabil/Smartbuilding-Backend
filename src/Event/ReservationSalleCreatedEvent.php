<?php

namespace App\Event;

use App\Entity\ReservationSalle;
use Symfony\Contracts\EventDispatcher\Event;
class ReservationSalleCreatedEvent extends  Event
{
    public const NAME = 'reservation.created';

    private ReservationSalle $reservationSalle;


    public function __construct(ReservationSalle $reservationSalle)
    {
        $this->reservationSalle = $reservationSalle;
    }

    public function getReservationSalle(): ReservationSalle
    {
        return $this->reservationSalle;
    }
}