<?php

namespace App\Entity;

use App\Repository\IncidentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: IncidentRepository::class)]
class Incident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('incident')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('incident')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100)]
    #[Groups('incident')]
    private ?string $localisation = null;

    #[ORM\Column(length: 100)]
    #[Groups('incident')]
    private ?string $categorie = null;

    #[ORM\Column(length: 100)]
    #[Groups('incident')]
    private ?string $statut = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups('incident')]
    private ?string $description = null;

    #[ORM\Column(length: 100)]
    #[Groups('incident')]
    private ?string $degre = null;

    #[ORM\OneToOne(inversedBy: 'incident', cascade: ['persist', 'remove'])]
    #[Groups('incident')]
    private ?Image $image = null;

    #[ORM\Column(length: 100)]
    #[Groups('incident')]
    private ?string $piece = null;

    #[ORM\ManyToOne(inversedBy: 'incident')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'incident', targetEntity: Notification::class)]
    private Collection $reservationCantine;

    #[ORM\OneToMany(mappedBy: 'incident', targetEntity: NotificationIncident::class, cascade: ['remove'])]
    private Collection $notificationIncidents;

    public function __construct()
    {
        $this->reservationCantine = new ArrayCollection();
        $this->notificationIncidents = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDegre(): ?string
    {
        return $this->degre;
    }

    public function setDegre(string $degre): self
    {
        $this->degre = $degre;

        return $this;
    }

    public function getImage(): ?string
    {
        if($this->image === null){
            return null;
        }
        else {
            return $this->image->getPath();
        }
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPiece(): ?string
    {
        return $this->piece;
    }

    public function setPiece(string $piece): self
    {
        $this->piece = $piece;

        return $this;
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

    #[Groups('incidents:user')]

    public function getUserId(): ?int
    {
        return $this->user ? $this->user->getId() : -1;
    }

    /**
     * @return Collection<int, NotificationIncident>
     */
    public function getNotificationIncidents(): Collection
    {
        return $this->notificationIncidents;
    }

    public function addNotificationIncident(NotificationIncident $notificationIncident): static
    {
        if (!$this->notificationIncidents->contains($notificationIncident)) {
            $this->notificationIncidents->add($notificationIncident);
            $notificationIncident->setIncident($this);
        }

        return $this;
    }

    public function removeNotificationIncident(NotificationIncident $notificationIncident): static
    {
        if ($this->notificationIncidents->removeElement($notificationIncident)) {
            // set the owning side to null (unless already changed)
            if ($notificationIncident->getIncident() === $this) {
                $notificationIncident->setIncident(null);
            }
        }

        return $this;
    }
}
