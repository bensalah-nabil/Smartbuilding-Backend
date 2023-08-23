<?php

namespace App\Event;

use App\Entity\ReservationCantine;
use Symfony\Contracts\EventDispatcher\Event;
class ReservationCantineCreatedEvent extends  Event
{
    public const NAME = 'reservation.created';

    private ReservationCantine $reservationCantine;


    public function __construct(ReservationCantine $reservationCantine)
    {
        $this->reservationCantine = $reservationCantine;
    }

    public function getReservationCantine(): ReservationCantine
    {
        return $this->reservationCantine;
    }
}