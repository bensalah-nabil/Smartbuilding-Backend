<?php

namespace App\Entity;

use App\Repository\FoodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: FoodRepository::class)]
#[Vich\Uploadable]
class Food
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu:food','reservation:foods'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['food','reservation:foods'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('food')]
    private ?string $category = null;

    #[ORM\Column]
    #[Groups(['food','reservation:foods'])]
    private ?float $price = null;

    #[ORM\OneToOne(inversedBy: 'food', cascade: ['persist', 'remove'])]
    #[Groups(['food','reservation:foods'])]
    private ?Image $image = null;

    #[ORM\OneToMany(mappedBy: 'food', targetEntity: RepasParJour::class)]
    #[Groups('menu')]
    private Collection $repasParJour;

    public function __construct()
    {
        $this->repasParJour = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        if ($this->image !== null) {
            return $this->image->getPath();
        }
        return null;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, RepasParJour>
     */
    public function getRepasParJour(): Collection
    {
        return $this->repasParJour;
    }

    public function addRepasParJour(RepasParJour $repasParJour): self
    {
        if (!$this->repasParJour->contains($repasParJour)) {
            $this->repasParJour->add($repasParJour);
            $repasParJour->setFood($this);
        }

        return $this;
    }

    public function removeRepasParJour(RepasParJour $repasParJour): self
    {
        if ($this->repasParJour->removeElement($repasParJour)) {
            // set the owning side to null (unless already changed)
            if ($repasParJour->getFood() === $this) {
                $repasParJour->setFood(null);
            }
        }

        return $this;
    }

}
