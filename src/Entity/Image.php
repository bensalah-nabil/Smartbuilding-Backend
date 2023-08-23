<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $size = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Path = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    #[Groups('food')]
    private ?Food $food = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    #[Groups('incidents')]
    private ?Incident $incident = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    #[Groups('salle')]
    private ?SalleReunion $salleReunion = null;

    #[ORM\OneToOne(mappedBy: 'image', cascade: ['persist', 'remove'])]
    #[Groups('user')]
    private ?User $user = null;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(?string $size): self
    {
        $this->size = $size;

        return $this;
    }


    public function getPath(): ?string
    {
        return $this->Path  ;
    }

    public function setPath(?string $Path): self
    {
        $this->Path = $Path;

        return $this;
    }

    public function getFood(): ?Food
    {
        return $this->food;
    }

    public function setFood(?Food $food): self
    {
        // unset the owning side of the relation if necessary
        if ($food === null && $this->food !== null) {
            $this->food->setImage(null);
        }

        // set the owning side of the relation if necessary
        if ($food !== null && $food->getImage() !== $this) {
            $food->setImage($this);
        }

        $this->food = $food;

        return $this;
    }

    public function getIncident(): ?Incident
    {
        return $this->incident;
    }

    public function setIncident(?Incident $incident): self
    {
        // unset the owning side of the relation if necessary
        if ($incident === null && $this->incident !== null) {
            $this->incident->setImage(null);
        }

        // set the owning side of the relation if necessary
        if ($incident !== null && $incident->getImage() !== $this) {
            $incident->setImage($this);
        }

        $this->incident = $incident;

        return $this;
    }

    public function getSalleReunion(): ?SalleReunion
    {
        return $this->salleReunion;
    }

    public function setSalleReunion(?SalleReunion $salleReunion): self
    {
        // unset the owning side of the relation if necessary
        if ($salleReunion === null && $this->salleReunion !== null) {
            $this->salleReunion->setImage(null);
        }

        // set the owning side of the relation if necessary
        if ($salleReunion !== null && $salleReunion->getImage() !== $this) {
            $salleReunion->setImage($this);
        }

        $this->salleReunion = $salleReunion;

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
}
