<?php

namespace App\Entity;

use App\Repository\NotificationIncidentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationIncidentRepository::class)]
class NotificationIncident extends Notification
{
    public function __construct()
    {
        parent::__construct();
        $this->setModule('incident');
    }
    

    #[ORM\ManyToOne(inversedBy: 'notificationIncidents')]
    private ?Incident $incident = null;


    #[Groups('notification')]
    public function getIncidentId(): ?int
    {
        return $this->incident->getId();
    }
    public function getIncident(): ?Incident
    {
        return $this->incident;
    }

    public function setIncident(?Incident $incident): static
    {
        $this->incident = $incident;
        return $this;
    }
}
