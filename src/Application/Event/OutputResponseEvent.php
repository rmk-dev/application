<?php

namespace Rmk\Application\Event;

use Rmk\Event\EventInterface;
use Rmk\Event\Traits\EventTrait;

/**
 * Class OutputResponseEvent
 *
 * @package Rmk\Application\Event
 */
class OutputResponseEvent implements EventInterface
{
    use EventTrait;
}
