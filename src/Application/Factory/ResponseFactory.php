<?php

namespace Rmk\Application\Factory;

use Psr\Container\ContainerInterface;
use Rmk\ServiceContainer\FactoryInterface;
use Rmk\Http\Factory\ResponseFactory as HttpResponseFactory;

/**
 * Class ResponseFactory
 *
 * @package Rmk\Application\Factory
 */
class ResponseFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $serviceContainer, $serviceName = null)
    {
        $httpFactory = new HttpResponseFactory();
        return $httpFactory->createResponse();
    }
}
