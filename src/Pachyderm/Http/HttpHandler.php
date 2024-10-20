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


    public function addHeaders(array $headers): HttpInterface
    {
        $this->_headers = array_merge($this->_headers, $headers);
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
        $httpCodes = [
            // 1xx: Informational
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Early Hints',

            // 2xx: Success
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',

            // 3xx: Redirection
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',

            // 4xx: Client Error
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Payload Too Large',
            414 => 'URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => "I'm a teapot", // RFC 2324
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Too Early',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',

            // 5xx: Server Error
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            510 => 'Not Extended',
            511 => 'Network Authentication Required',
        ];

        return $httpCodes[$code] ?? null;
    }
}
