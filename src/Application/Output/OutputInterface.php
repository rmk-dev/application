<?php

namespace Rmk\Application\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface OutputInterface
 * @package Rmk\Application\Output
 */
interface OutputInterface
{

    /**
     * Outputs the response content
     *
     * @param ResponseInterface $response
     */
    public function output(ResponseInterface $response): void;
}
