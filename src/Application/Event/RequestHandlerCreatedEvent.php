<?php

namespace Rmk\Application\Event;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RequestHandlerCreatedEvent
 * @package Rmk\Application\Event
 */
class RequestHandlerCreatedEvent extends ApplicationEvent
{

    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->getParam('request_handler');
    }
}
