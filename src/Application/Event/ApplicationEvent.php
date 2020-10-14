<?php

namespace Rmk\Application\Event;

use Rmk\Application\Application;
use Rmk\Event\EventInterface;
use Rmk\Event\Traits\EventTrait;

class ApplicationEvent implements EventInterface
{
    use EventTrait;

    public function getApplication(): Application
    {
        return $this->getEmitter();
    }
}
