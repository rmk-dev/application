<?php

namespace Rmk\Application\Factory;

use Psr\Container\ContainerInterface;
use Rmk\Http\Factory\ServerRequestFactory;
use Rmk\ServiceContainer\FactoryInterface;

/**
 * Class RequestFactory
 *
 * @package Rmk\Application\Factory
 */
class RequestFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $serviceContainer, $serviceName = null)
    {
        $httpFactory = new ServerRequestFactory();
        return $httpFactory->createFromSuperglobals();
    }
}