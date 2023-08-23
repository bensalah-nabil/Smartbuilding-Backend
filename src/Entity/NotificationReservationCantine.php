<?php

namespace App\Entity;

use App\Repository\NotificationReservationCantineRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationReservationCantineRepository::class)]
class NotificationReservationCantine extends Notification
{
    public function __construct()
    {
        parent::__construct();
        $this->setModule('cantine');
    }


    #[ORM\ManyToOne(inversedBy: 'notificationReservationCantines')]
    private ?ReservationCantine $reservation = null;

    #[Groups('notification')]
    public function getReservationId(): ?int
    {
        return $this->reservation->getId();
    }

    public function getReservation(): ?ReservationCantine
    {
        return $this->reservation;
    }


    public function setReservation(?ReservationCantine $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }
}
