<?php

namespace Rmk\Application\Factory;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Rmk\Event\EventDispatcher;
use Rmk\ServiceContainer\FactoryInterface;

class EventDispatcherFactory implements FactoryInterface
{

    /**
     * Creates and returns the service
     *
     * @param ContainerInterface $serviceContainer The service container
     * @param string|null $serviceName The service name
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $serviceContainer, $serviceName = null)
    {
        return new EventDispatcher($serviceContainer->get(ListenerProviderInterface::class));
    }
}
