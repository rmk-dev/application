<?php

namespace Rmk\Application\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Router\RouterServiceInterface;
use Rmk\ServiceContainer\ServiceContainerInterface;

class ApplicationInitEvent extends ApplicationEvent
{

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getParam('event_dispatcher');
    }

    public function getRouter(): RouterServiceInterface
    {
        return $this->getParam('router');
    }

    public function getServiceContainer(): ServiceContainerInterface
    {
        return $this->getParam('service_container');
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->getParam('request');
    }

    public function getResponse(): ResponseInterface
    {
        return $this->getParam('response');
    }

    public function getInitialized(): bool
    {
        return (bool) $this->getParam('initialized');
    }
}
