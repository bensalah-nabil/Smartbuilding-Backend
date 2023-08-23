<?php

namespace App\Entity;

use App\Repository\MailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRepository::class)]
class Mail
{
    public const EVENT_UNKNOWN = 'unknown';
    public const EVENT_SENT = 'request';
    public const EVENT_CLICKED = 'click';
    public const EVENT_DEFERRED = 'deferred';
    public const EVENT_DELIVERED = 'delivered';
    public const EVENT_SOFT_BOUNCED = 'soft_bounce';
    public const EVENT_HARD_BOUNCED = 'hard_bounce';
    public const EVENT_COMPLAINT = 'complaint';
    public const EVENT_FIRST_OPENING = 'unique_opened';
    public const EVENT_OPENED = 'opened';
    public const EVENT_INVALID_EMAIL = 'invalid_email';
    public const EVENT_BLOCKED = 'blocked';
    public const EVENT_ERROR = 'error';
    public const EVENT_UNSUBSCRIBE = 'unsubscribed';
    public const EVENT_PROXY_OPENED = 'proxy_open';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $messageId;

    #[ORM\Column(type: 'string', length: 20)]
    private $lastEvent = self::EVENT_UNKNOWN;

    #[ORM\Column(type: 'datetime')]
    private $created;

    #[ORM\Column(type: 'datetime')]
    private $updated;

    public function __construct()
    {
        $now = new \DateTimeImmutable();

        $this->created = $now;
        $this->updated = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getLastEvent(): ?string
    {
        return $this->lastEvent;
    }

    public function setLastEvent(string $lastEvent): self
    {
        $this->lastEvent = $lastEvent;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function isUpdateable(\DateTimeInterface $date): bool
    {
        return self::EVENT_UNKNOWN === $this->getLastEvent() || $this->getUpdated() <= $date;
    }

    public function getDisplayMode(): string
    {
        if (in_array($this->getLastEvent(), [self::EVENT_DELIVERED, self::EVENT_FIRST_OPENING, self::EVENT_OPENED, self::EVENT_CLICKED, self::EVENT_PROXY_OPENED])) {
            return 'success';
        }

        if (self::EVENT_SENT === $this->getLastEvent()) {
            return 'primary';
        }

        if (self::EVENT_DEFERRED === $this->getLastEvent()) {
            return 'warning';
        }

        if (self::EVENT_UNKNOWN === $this->getLastEvent()) {
            return 'secondary';
        }

        return 'danger';
    }

    public function getIcon(): string
    {
        if (in_array($this->getLastEvent(), [self::EVENT_FIRST_OPENING, self::EVENT_OPENED, self::EVENT_CLICKED])) {
            return 'check-circle';
        }

        if (in_array($this->getLastEvent(), [self::EVENT_DELIVERED, self::EVENT_PROXY_OPENED])) {
            return 'check-circle-o';
        }

        if (self::EVENT_SENT === $this->getLastEvent()) {
            return 'spinner fa-spin';
        }

        if (self::EVENT_DEFERRED === $this->getLastEvent()) {
            return 'hourglass-half';
        }

        if (self::EVENT_UNKNOWN === $this->getLastEvent()) {
            return 'envelope-o';
        }

        if (in_array($this->getLastEvent(), [self::EVENT_HARD_BOUNCED, self::EVENT_COMPLAINT, self::EVENT_BLOCKED, self::EVENT_INVALID_EMAIL])) {
            return 'times-circle';
        }

        return 'times-circle-o';
    }
}
