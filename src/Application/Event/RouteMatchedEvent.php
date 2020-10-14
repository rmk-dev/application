<?php

namespace Rmk\Application\Event;

use Rmk\Router\Route;

/**
 * Class RouteMatchedEvent
 * @package Rmk\Application\Event
 */
class RouteMatchedEvent extends ApplicationEvent
{

    public function getMatchedRoute(): Route
    {
        return $this->getParam('matched_route');
    }
}
