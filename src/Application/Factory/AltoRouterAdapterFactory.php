<?php

namespace Rmk\Application\Factory;

use AltoRouter;
use Psr\Container\ContainerInterface;
use Rmk\Router\Adapter\AltoRouterAdapter;
use Rmk\ServiceContainer\FactoryInterface;

/**
 * Class AltoRouterAdapterFactory
 *
 * @package Rmk\Application\Factory
 */
class AltoRouterAdapterFactory implements FactoryInterface
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
        return new AltoRouterAdapter(new AltoRouter());
    }
}
