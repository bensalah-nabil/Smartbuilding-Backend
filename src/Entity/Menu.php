<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('menu:food')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups('menu:food')]
    private ?\DateTimeInterface $date;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: RepasParJour::class)]
    #[Groups('menu:food')]
    private Collection $repasParJour;

    public function __construct()
    {
        $this->repasParJour = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->date->format('Y-m-d');
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, repasParJour>
     */
    public function getRepasParJour(): Collection
    {
        return $this->repasParJour;
    }

    public function addRepasParJour(RepasParJour $repasParJour): self
    {
        if (!$this->repasParJour->contains($repasParJour)) {
            $this->repasParJour->add($repasParJour);
            $repasParJour->setMenu($this);
        }

        return $this;
    }

    public function removeRepasParJour(RepasParJour $repasParJour): self
    {
        if ($this->repasParJour->removeElement($repasParJour)) {
            // set the owning side to null (unless already changed)
            if ($repasParJour->getMenu() === $this) {
                $repasParJour->setMenu(null);
            }
        }

        return $this;
    }

}
