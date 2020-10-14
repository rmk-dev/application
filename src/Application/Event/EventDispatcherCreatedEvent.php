<?php

namespace Rmk\Application\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Rmk\Event\EventInterface;
use Rmk\Event\Traits\EventTrait;

class EventDispatcherCreatedEvent extends ApplicationEvent
{

    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->getParam('event_dispatcher');
    }
}
