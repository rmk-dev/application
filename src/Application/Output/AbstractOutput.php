<?php

namespace Rmk\Application\Output;

use Psr\Http\Message\ResponseInterface;
use Rmk\Application\Event\OutputResponseEvent;
use Rmk\Event\Traits\EventDispatcherAwareTrait;

/**
 * Class AbstractOutput
 * @package Rmk\Application\Output
 */
abstract class AbstractOutput implements OutputInterface
{
    use EventDispatcherAwareTrait;

    /**
     * @param ResponseInterface $response
     */
    abstract public function outputResponse(ResponseInterface $response): void;

    /**
     * @param ResponseInterface $response
     */
    public function output(ResponseInterface $response): void
    {
        $this->outputResponse($response);
        $this->dispatchEvent(OutputResponseEvent::class, ['response' => $response]);
    }
}
