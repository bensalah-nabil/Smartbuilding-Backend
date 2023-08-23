<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]

class UserChangeNotifier
{
    // the entity listener methods receive two arguments:
    // the entity instance and the lifecycle event
    public function postUpdate(User $user, LifecycleEventArgs $event): void
    {
        // ... do something to notify the changes
    }
}