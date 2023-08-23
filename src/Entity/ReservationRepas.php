<?php

namespace App\Entity;

use App\Repository\ReservationRepasRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ReservationRepasRepository::class)]
class ReservationRepas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'reservationRepas')]
    #[Groups('reservation:foods')]
    private ?RepasParJour $repas = null;

    #[ORM\ManyToOne(inversedBy: 'reservationRepas')]
    private ?ReservationCantine $reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getRepas(): ?RepasParJour
    {
        return $this->repas;
    }

    public function setRepas(?RepasParJour $repas): self
    {
        $this->repas = $repas;

        return $this;
    }

    public function getReservation(): ?ReservationCantine
    {
        return $this->reservation;
    }

    public function setReservation(?ReservationCantine $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }
  }
