<?php

namespace Rmk\Application\Output;

use Psr\Http\Message\ResponseInterface;

/**
 * Class DefaultOutput
 *
 * @package Rmk\Application\Output
 */
class DefaultOutput extends AbstractOutput
{

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    public function outputResponse(ResponseInterface $response): void
    {
        $this->response = $response;
        $this->writeHeaders();
        $this->writeBody();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function writeHeaders(): void
    {
        if (PHP_SAPI !== 'cli') {
            header($this->getMainLine(), true);
            foreach (array_keys($this->response->getHeaders()) as $name) {
                header($this->getHeaderLine($name));
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getMainLine(): string
    {
        return sprintf(
            'HTTP/%s %s %s',
            $this->response->getProtocolVersion(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase()
        );
    }

    /**
     * @param string $name
     * @codeCoverageIgnore
     */
    protected function getHeaderLine(string $name): string
    {
        return sprintf('%s: %s', $name, $this->response->getHeaderLine($name));
    }

    protected function writeBody(): void
    {
        $body = $this->response->getBody();
        if ($body && $body->getSize()) {
            $fp = fopen('php://output', 'a');
            fwrite($fp, $body . '');
            fclose($fp);
        }
    }
}
