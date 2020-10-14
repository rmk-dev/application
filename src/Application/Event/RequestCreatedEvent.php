<?php

namespace Rmk\Application\Event;

use Psr\Http\Message\ServerRequestInterface;

class RequestCreatedEvent extends ApplicationEvent
{

    public function getRequest(): ServerRequestInterface
    {
        return $this->getParam('request');
    }
}
