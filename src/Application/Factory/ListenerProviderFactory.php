<?php

namespace Rmk\Application\Factory;

use Psr\Container\ContainerInterface;
use Rmk\CallbackResolver\CallbackResolver;
use Rmk\Container\Container;
use Rmk\Event\ListenerProvider;
use Rmk\ServiceContainer\FactoryInterface;
use Rmk\ServiceContainer\ServiceContainer;

/**
 * Class ListenerProviderFactory
 *
 * @package Rmk\Application\Factory
 */
class ListenerProviderFactory implements FactoryInterface
{

    protected function addEventListeners(ListenerProvider $provider, array $events)
    {
        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                // TODO Check priority
                $provider->addEventListener($eventName, $listener);
            }
        }
    }

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
        $provider = new ListenerProvider($serviceContainer->get(CallbackResolver::class));
        /** @var Container $config */
        $config = $serviceContainer->get(ServiceContainer::CONFIG_KEY);
        if ($config->has('event_listeners')) {
            $this->addEventListeners($provider, $config->get('event_listeners'));
        }
        return $provider;
    }
}
