<?php

namespace App\Entity;

use App\Repository\NotificationReservationSalleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationReservationSalleRepository::class)]
class NotificationReservationSalle extends Notification
{

    public function __construct()
    {
        parent::__construct();
        $this->setModule('salle');
    }



    #[ORM\ManyToOne(inversedBy: 'notificationReservationSalles')]
    private ?ReservationSalle $reservation = null;


    #[Groups('notification')]
    public function getReservationId(): ?int
    {
        return $this->reservation->getId();
    }

    public function getReservation(): ?ReservationSalle
    {
        return $this->reservation;
    }

    public function setReservation(?ReservationSalle $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }
}
