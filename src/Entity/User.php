<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('user')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups('user')]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('user')]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $poste = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups('user')]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('user')]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('user')]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $superieur = null;

    #[ORM\Column]
    #[Groups('user')]
    private array $roles = [];

    #[ORM\Column]
    #[Groups('user')]
    private ?bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ReservationSalle::class)]
    private Collection $reservationSalle;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Incident::class)]
    private Collection $incidents;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ReservationCantine::class)]
    private Collection $reservationCantines;


    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups('food')]
    private ?Image $image = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->incidents = new ArrayCollection();
        $this->reservationSalle = new ArrayCollection();
        $this->reservationCantines = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email ?: null;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, ReservationSalle>
     */
    public function getReservationSalle(): Collection
    {
        return $this->reservationSalle;
    }

    public function addReservationSalle(ReservationSalle $reservationSalle): self
    {
        if (!$this->reservationSalle->contains($reservationSalle)) {
            $this->reservationSalle->add($reservationSalle);
            $reservationSalle->setUser($this);
        }

        return $this;
    }

    public function removeReservationSalle(ReservationSalle $reservationSalle): self
    {
        if ($this->reservationSalle->removeElement($reservationSalle)) {
            // set the owning side to null (unless already changed)
            if ($reservationSalle->getUser() === $this) {
                $reservationSalle->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Incident>
     */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): self
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setUser($this);
        }

        return $this;
    }

    public function removeIncident(Incident $incident): self
    {
        if ($this->incidents->removeElement($incident)) {
            // set the owning side to null (unless already changed)
            if ($incident->getUserId() === $this) {
                $incident->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservationCantine>
     */
    public function getReservationCantines(): Collection
    {
        return $this->reservationCantines;
    }

    public function addReservationCantine(ReservationCantine $reservationCantine): self
    {
        if (!$this->reservationCantines->contains($reservationCantine)) {
            $this->reservationCantines->add($reservationCantine);
            $reservationCantine->setUser($this);
        }

        return $this;
    }

    public function removeReservationCantine(ReservationCantine $reservationCantine): self
    {
        if ($this->reservationCantines->removeElement($reservationCantine)) {
            // set the owning side to null (unless already changed)
            if ($reservationCantine->getUser() === $this) {
                $reservationCantine->setUser(null);
            }
        }

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): self
    {
        $this->poste = $poste;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->roles = [];
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getSuperieur(): ?string
    {
        return $this->superieur;
    }

    public function setSuperieur(?string $superieur): self
    {
        $this->superieur = $superieur;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        // unset the owning side of the relation if necessary
        if ($image === null && $this->image !== null) {
            $this->image->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($image !== null && $image->getUser() !== $this) {
            $image->setUser($this);
        }

        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }
}
