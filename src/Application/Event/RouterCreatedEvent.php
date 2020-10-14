<?php

/**
 *
 */
namespace Rmk\Application\Event;

use Rmk\Router\RouterServiceInterface;

/**
 * Class RouterCreatedEvent
 *
 * @package Rmk\Application\Event
 */
class RouterCreatedEvent extends ApplicationEvent
{

    public function getRouter(): RouterServiceInterface
    {
        return $this->getParam('router');
    }
}
