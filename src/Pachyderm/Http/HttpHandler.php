<?php

namespace Pachyderm\Http;

class HttpHandler implements HttpInterface
{
    protected $_statusCode;
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

    public function path(): string
    {
        $uri = $this->uri();
        $end = strpos($uri, '?');
        return substr($uri, 0, $end ? $end : strlen($uri));
    }

    public function body(): string | null
    {
        return file_get_contents('php://input');
    }

    public function bodyParams(): array|null
    {
        return json_decode($this->body(), true);
    }

    public function setStatusCode(int $statusCode): HttpInterface
    {
        $this->_statusCode = $statusCode;
        return $this;
    }

    public function setHeader(string $header, string $value): HttpInterface
    {
        $this->_headers[$header] = $value;
        return $this;
    }

    public function setBody(string $body): HttpInterface
    {
        $this->_body = $body;
        return $this;
    }

    public function send(): void
    {
        $httpCode = $this->httpCode($this->_statusCode);
        header('HTTP/1.1 ' . $this->_statusCode . ' ' . $httpCode);
        foreach ($this->_headers as $header => $value) {
            header($header . ': ' . $value);
        }
        echo $this->_body;
    }

    protected function httpCode(string|int $code): string|null
    {
        switch ($code) {
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not found';
            case 412:
                return 'Precondition Failed';
            case 500:
                return 'Internal error';
        }
        return NULL;
    }
}
