<?php

namespace Rmk\Application\Event;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ApplicationOutput
 * @package Rmk\Application\Event
 */
class ApplicationOutput extends ApplicationEvent
{

    public function getResponse(): ResponseInterface
    {
        return $this->getParam('response');
    }
}
