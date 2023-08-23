<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['equipement'])]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 100)]
    #[Groups(['equipement'])]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: SalleReunion::class, mappedBy: 'equipements')]
    private Collection $salles;

    public function __construct()
    {
        $this->salles = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
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
    /**
     * @return Collection<int, SalleReunion>
     */
    public function getSalleReunions(): Collection
    {
        return $this->salles;
    }
    public function addSalleReunion(SalleReunion $salle): self
    {
        if (!$this->salles->contains($salle)) {
            $this->salles->add($salle);
            $salle->addEquipement($this);
        }
        return $this;
    }
    public function removeSalleReunion(SalleReunion $salle): self
    {
            if ($this->salles->removeElement($salle)) {
            $salle->removeEquipement($this);
        }
        return $this;
    }
}
