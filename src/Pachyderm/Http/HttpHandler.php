<?php

namespace Pachyderm\Http;

class HttpHandler implements HttpInterface
{
    protected array $_headers = array();
    protected string $_body;

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function body(): string | null
    {
        return file_get_contents('php://input');
    }

    public function setHeader(string $header): HttpInterface
    {
        $this->_headers[] = $header;
        return $this;
    }

    public function setBody(string $body): HttpInterface
    {
        $this->_body = $body;
        return $this;
    }

    public function send(): void
    {
        foreach ($this->_headers as $header) {
            header($header);
        }
        echo $this->_body;
    }
}
