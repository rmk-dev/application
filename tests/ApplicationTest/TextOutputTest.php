<?php

namespace ApplicationTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rmk\Application\Output\TextOutput;

class TextOutputTest extends TestCase
{

    public function testOutput(): void
    {
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $response->method('getProtocolVersion')->willReturn('1.1');
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getReasonPhrase')->willReturn('OK');
        $response->method('getHeaders')->willReturn([
            'Content-Type' => ['text/html'],
        ]);
        $content = '<!DOCTYPE html><html><head><title>Test Title</title></head><body><h1>Test Content</h1></body></html>';
        $body = $this->getMockForAbstractClass(StreamInterface::class);
        $body->method('getSize')->willReturn(strlen($content));
        $body->method('__toString')->willReturn($content);
        $response->method('getBody')->willReturn($body);
        $output = new TextOutput();
        $output->output($response);
        $actualOutput = $this->getActualOutput();
        $this->assertStringStartsWith('HTTP/1.1 200 OK', $actualOutput);
        $this->assertStringContainsString('Content-Type: text/html', $actualOutput);
        $this->assertStringEndsWith($content, $actualOutput);
    }
}
