<?php

// config/services.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\User;
use App\EventListener\DatabaseActivitySubscriber;
use App\EventListener\SearchIndexer;
use App\EventListener\UserChangeNotifier;
use App\Service\UploaderService;

return static function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();
    $services->set(SearchIndexer::class)
        ->tag('doctrine.event_listener', [
            // this is the only required option for the lifecycle listener tag
            'event' => 'postPersist',

            // listeners can define their priority in case multiple subscribers or listeners are associated
            // to the same event (default priority = 0; higher numbers = listener is run earlier)
            'priority' => 500,

            # you can also restrict listeners to a specific Doctrine connection
            'connection' => 'default',
        ]);
    $services->set(UploaderService::class)
        ->arg('$targetDirectory', '%foods_directory%')
    ;
    $services = $containerConfigurator->services();

    $services->set(UserChangeNotifier::class)
        ->tag('doctrine.orm.entity_listener', [
            // These are the options required to define the entity listener:
            'event' => 'postUpdate',
            'entity' => User::class,

            // These are other options that you may define if needed:

            // set the 'lazy' option to TRUE to only instantiate listeners when they are used
            // 'lazy' => true,

            // set the 'entity_manager' option if the listener is not associated to the default manager
            // 'entity_manager' => 'custom',

            // by default, Symfony looks for a method called after the event (e.g. postUpdate())
            // if it doesn't exist, it tries to execute the '__invoke()' method, but you can
            // configure a custom method name with the 'method' option
            // 'method' => 'checkUserChanges',
        ])
    ;
    $services = $containerConfigurator->services();

    $services->set(DatabaseActivitySubscriber::class)
        ->tag('doctrine.event_subscriber',[
            'priority'=>500,
            'connection' => 'default'
        ])
    ;
};