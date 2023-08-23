<?php

namespace App\Entity;

use App\Repository\ReservationCantineRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationCantineRepository::class)]
class ReservationCantine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?string $statut = null;

    #[ORM\Column]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?float $total = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?DateTimeInterface $dateReservation = null;
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['repas:reservations','reservation:foods'])]
    private ?DateTimeInterface $dateConfirmation = null;

    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: ReservationRepas::class)]
    #[Groups(['repas:reservations','reservation:foods'])]
    private Collection $reservationRepas;

    #[ORM\ManyToOne(inversedBy: 'reservationCantines')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: NotificationReservationCantine::class, cascade: ['remove'])]
    private Collection $notificationReservationCantines;

    public function __construct()
    {
        $this->reservationRepas = new ArrayCollection();
        $this->notificationReservationCantines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getDateReservation(): ?String
    {
        return $this->dateReservation?->format('Y-m-d H:i:s');
    }

    public function setDateReservation(DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getDateConfirmation(): ?String
    {
        return $this->dateConfirmation?->format('Y-m-d H:i:s');
    }

    public function setDateConfirmation(?DateTimeInterface $dateConfirmation): self
    {
        $this->dateConfirmation = $dateConfirmation;

        return $this;
    }

    /**
     * @return Collection<int, ReservationRepas>
     */
    public function getReservationRepas(): Collection
    {
        return $this->reservationRepas;
    }

    public function addReservationRepas(ReservationRepas $reservationRepas): self
    {
        if (!$this->reservationRepas->contains($reservationRepas)) {
            $this->reservationRepas->add($reservationRepas);
            $reservationRepas->setReservation($this);
        }

        return $this;
    }

    public function removeReservationRepas(ReservationRepas $reservationRepas): self
    {
        if ($this->reservationRepas->removeElement($reservationRepas)) {
            // set the owning side to null (unless already changed)
            if ($reservationRepas->getReservation() === $this) {
                $reservationRepas->setReservation(null);
            }
        }

        return $this;
    }

    #[Groups('user')]
    public function getUserId(): int
    {
        return  $this->user ? $this->user->getId() : -1;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, NotificationReservationCantine>
     */
    public function getNotificationReservationCantines(): Collection
    {
        return $this->notificationReservationCantines;
    }

    public function addNotificationReservationCantine(NotificationReservationCantine $notificationReservationCantine): static
    {
        if (!$this->notificationReservationCantines->contains($notificationReservationCantine)) {
            $this->notificationReservationCantines->add($notificationReservationCantine);
            $notificationReservationCantine->setReservation($this);
        }

        return $this;
    }

    public function removeNotificationReservationCantine(NotificationReservationCantine $notificationReservationCantine): static
    {
        if ($this->notificationReservationCantines->removeElement($notificationReservationCantine)) {
            // set the owning side to null (unless already changed)
            if ($notificationReservationCantine->getReservation() === $this) {
                $notificationReservationCantine->setReservation(null);
            }
        }

        return $this;
    }
}
