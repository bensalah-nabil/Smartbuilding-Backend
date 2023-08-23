<?php

namespace App\Entity;

use App\Repository\ReservationSalleRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationSalleRepository::class)]
class ReservationSalle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?DateTimeInterface $dateReservation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?DateTimeInterface $dateConfirmation = null;

    #[ORM\ManyToOne(inversedBy: 'reservationSalles')]
    #[Groups('reservation:notif')]
    private ?SalleReunion $salle = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['reservations:salle','reservation:notif'])]
    private ?DateTimeInterface $dateFin = null;

    #[ORM\ManyToOne(inversedBy: 'reservationSalle')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: NotificationReservationSalle::class, cascade: ['remove'])]
    private Collection $notificationReservationSalles;

    public function __construct()
    {
        $this->notificationReservationSalles = new ArrayCollection();
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

    public function getDateDebut(): ?String
    {
        return $this->dateDebut?->format('Y-m-d H:i:s');
    }

    public function setDateDebut(DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?String
    {
        return $this->dateFin?->format('Y-m-d H:i:s');
    }

    public function setDateFin(DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    #[Groups('reservations:salle')]
    public function getSalleDeReunionId(): ?int
    {
        return $this->salle ? $this->salle->getId() : -1;
    }

    public function getSalle(): ?SalleReunion
    {
        if ($this->salle === null) {
            return null;
        }
        return $this->salle;
    }
    public function setSalle(?SalleReunion $salle): self
    {
        $this->salle = $salle;

        return $this;
    }

    public function getDateReservation(): ?String
    {
        return $this->dateReservation?->format('Y-m-d H:i:s');
    }

    public function setDateReservation(?DateTimeInterface $dateReservation): self
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
     * @return Collection<int, NotificationReservationSalle>
     */
    public function getNotificationReservationSalles(): Collection
    {
        return $this->notificationReservationSalles;
    }

    public function addNotificationReservationSalle(NotificationReservationSalle $notificationReservationSalle): static
    {
        if (!$this->notificationReservationSalles->contains($notificationReservationSalle)) {
            $this->notificationReservationSalles->add($notificationReservationSalle);
            $notificationReservationSalle->setReservation($this);
        }

        return $this;
    }

    public function removeNotificationReservationSalle(NotificationReservationSalle $notificationReservationSalle): static
    {
        if ($this->notificationReservationSalles->removeElement($notificationReservationSalle)) {
            // set the owning side to null (unless already changed)
            if ($notificationReservationSalle->getReservation() === $this) {
                $notificationReservationSalle->setReservation(null);
            }
        }

        return $this;
    }
}
