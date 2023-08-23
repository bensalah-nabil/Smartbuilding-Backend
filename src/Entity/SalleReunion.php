<?php

namespace App\Entity;

use App\Repository\SalleReunionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SalleReunionRepository::class)]
class SalleReunion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('salle')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('salle')]
    private ?string $nom = null;
    #[ORM\Column(length: 100)]
    #[Groups('salle')]
    private ?string $emplacement = null;
    #[ORM\Column]
    #[Groups('salle')]
    private ?int $capacite = null;
    #[ORM\Column(length: 100)]
    #[Groups('salle')]
    private ?string $statut = null;
    #[ORM\OneToMany(mappedBy: 'salle', targetEntity: ReservationSalle::class)]
    private Collection $reservationSalles;

    #[ORM\OneToOne(inversedBy: 'salleReunion', cascade: ['persist', 'remove'])]
    #[Groups('salle')]
    private ?Image $image = null;
    #[ORM\ManyToMany(targetEntity: Equipement::class, inversedBy: 'salleReunions')]
    private Collection $equipements;

    public function __construct()
    {
        $this->reservationSalles = new ArrayCollection();
        $this->equipements = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }
    public function setEmplacement(string $emplacement): self
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }
    public function setCapacite(int $capacite): self
    {
        $this->capacite = $capacite;

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
    public function getReservationSalles(): Collection
    {
        return $this->reservationSalles;
    }
    public function addReservationSalle(ReservationSalle $reservationSalle): self
    {
        if (!$this->reservationSalles->contains($reservationSalle)) {
            $this->reservationSalles->add($reservationSalle);
            $reservationSalle->setSalle($this);
        }
        return $this;
    }
    public function removeReservationSalle(ReservationSalle $reservationSalle): self
    {
        if ($this->reservationSalles->removeElement($reservationSalle)) {
            // set the owning side to null (unless already changed)
            if ($reservationSalle->getSalle() === $this) {
                $reservationSalle->setSalle(null);
            }
        }
        return $this;
    }
    public function getImage(): ?string
    {
        return $this->image?->getPath();
    }
    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Equipement>
     */
    public function getEquipements(): Collection
    {
        return $this->equipements;
    }

    public function addEquipement(Equipement $equipement): self
    {
        if (!$this->equipements->contains($equipement)) {
            $this->equipements->add($equipement);
        }

        return $this;
    }

    public function removeEquipement(Equipement $equipement): self
    {
        $this->equipements->removeElement($equipement);

        return $this;
    }
}
