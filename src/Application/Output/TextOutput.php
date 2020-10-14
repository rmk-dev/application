<?php

namespace Rmk\Application\Output;

use Psr\Http\Message\ResponseInterface;

use function GuzzleHttp\Psr7\str;

/**
 * Class TextOutput
 * @package Rmk\Application\Output
 */
class TextOutput extends AbstractOutput
{

    public function outputResponse(ResponseInterface $response): void
    {
        echo str($response);
    }
}
