<?php

namespace Rmk\Application\Event;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseCreatedEvent
 *
 * @package Rmk\Application\Event
 */
class ResponseCreatedEvent extends ApplicationEvent
{

    public function getResponse(): ResponseInterface
    {
        return $this->getParam('response');
    }
}
