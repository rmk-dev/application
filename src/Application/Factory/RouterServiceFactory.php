<?php

namespace Rmk\Application\Factory;

use Psr\Container\ContainerInterface;
use Rmk\Container\Container;
use Rmk\Router\Adapter\RouterAdapterInterface;
use Rmk\Router\RouterService;
use Rmk\ServiceContainer\FactoryInterface;
use Rmk\ServiceContainer\ServiceContainer;

/**
 * Class RouterFactory
 *
 * @package Rmk\Application\Factory
 */
class RouterServiceFactory implements FactoryInterface
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
        $adapter = $serviceContainer->get(RouterAdapterInterface::class);
        $service = new RouterService($adapter);
        /** @var Container $config */
        $config = $serviceContainer->get(ServiceContainer::CONFIG_KEY);
        if ($config->has('routes')) {
            $service->loadFromConfig($config->get('routes'));
        }
        return $service;
    }
}
