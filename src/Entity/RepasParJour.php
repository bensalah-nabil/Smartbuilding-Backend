<?php

namespace App\Entity;

use App\Repository\RepasParJourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: RepasParJourRepository::class)]
class RepasParJour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('menu:food')]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups('menu:food')]
    private ?int $stock = null;

    #[ORM\ManyToOne(inversedBy: 'repasParJour')]
    #[Groups('menu')]
    private ?Menu $menu = null;

    #[ORM\ManyToOne(inversedBy: 'repasParJour')]
    #[Groups('reservation:foods')]
    private ?Food $food = null;

    #[ORM\OneToMany(mappedBy: 'repas', targetEntity: ReservationRepas::class)]
    private Collection $reservationRepas;

    public function __construct()
    {
        $this->reservationRepas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    #[Groups(['menu:food'])]
    public function getRepasId(): int
    {
        return  $this->food ? $this->food->getId() : -1;
    }
    public function getFood(): ?Food
    {
        if ($this->food === null) {
            return null;
        }
        return $this->food;
    }

    public function setFood(?Food $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

//    #[Groups('repas:reservations')]
//    public function getReservationRepasId(): int
//    {
//        return  $this->reservationRepas ? $this->reservationRepas->getId() : -1;
//    }

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
            $reservationRepas->setRepas($this);
        }

        return $this;
    }

    public function removeReservationRepas(ReservationRepas $reservationRepas): self
    {
        if ($this->reservationRepas->removeElement($reservationRepas)) {
            // set the owning side to null (unless already changed)
            if ($reservationRepas->getRepas() === $this) {
                $reservationRepas->setRepas(null);
            }
        }

        return $this;
    }
}
