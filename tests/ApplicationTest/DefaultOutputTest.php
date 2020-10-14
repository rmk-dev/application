<?php

namespace Rmk\ApplicationTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rmk\Application\Output\DefaultOutput;

class DefaultOutputTest extends TestCase
{

    public function testOutput()
    {
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $body = $this->getMockForAbstractClass(StreamInterface::class);
        $body->method('getSize')->willReturn(strlen('Test Content'));
        $body->method('__toString')->willReturn('Test Content');
        $response->method('getBody')->willReturn($body);
        $this->expectOutputString('Test Content');
        $output = new DefaultOutput();
        $output->output($response);
    }
}
