<?php

namespace Rmk\Application\Factory;

use Ds\Queue;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Rmk\Application\Application;
use Rmk\Http\RequestHandler;
use Rmk\ServiceContainer\FactoryInterface;

/**
 * Class RequestHandlerFactory
 *
 * @package Rmk\Application\Factory
 */
class RequestHandlerFactory implements FactoryInterface
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
        $response = $serviceContainer->get(ResponseInterface::class);
        $eventDispatcher = $serviceContainer->get(EventDispatcherInterface::class);
        $middlewares = new Queue();
        $app = $serviceContainer->get(Application::class);
        $route = $app->getMatchedRoute();

        return new RequestHandler($response, $eventDispatcher, $middlewares, $route);
    }
}
