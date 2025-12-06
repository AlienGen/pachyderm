<?php

namespace Pachyderm\Exchange;

/**
 * Class Response
 *
 * This class represents an HTTP response, implementing the ArrayAccess interface
 * for retro-compatibility with middleware. It provides methods to set and retrieve
 * HTTP status codes, headers, and body content. Additionally, it includes static
 * methods for common HTTP response scenarios.
 */
class Response implements \Countable, \ArrayAccess
{
    private int $statusCode; // HTTP status code
    private array $headers;    // Array of HTTP headers
    private mixed $body;       // Response body content

    /**
     * Constructor to initialize the response with a status code, body, and headers.
     *
     * @param int $statusCode HTTP status code, default is 200.
     * @param mixed $body Response body content.
     * @param array $headers Array of HTTP headers.
     */
    public function __construct(int $statusCode = 200, mixed $body = null, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Set a header value.
     *
     * @param string $header Header name.
     * @param string $value Header value.
     * @return $this
     */
    public function header(string $header, string $value): Response {
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function statusCode(): int {
        return $this->statusCode;
    }

    /**
     * Get all headers.
     *
     * @return array
     */
    public function headers(): array {
        return $this->headers;
    }

    /**
     * Get the response body.
     *
     * @return mixed
     */
    public function body(): mixed {
        return $this->body;
    }

    /**
     * Method for retro-compatibility with the array access interface inside middleware.
     *
     * @param int $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return $offset == 0 || $offset == 1 || $offset == 2;
    }

    /**
     * Method for retro-compatibility with the array access interface inside middleware.
     *
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        if($offset == 0) {
            return $this->statusCode;
        }

        if($offset == 1) {
            return $this->body;
        }

        if($offset == 2) {
            return $this->headers;
        }

        return null;
    }

    /**
     * Method for retro-compatibility with the array access interface inside middleware.
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if($offset == 0) {
            $this->statusCode = $value;
        }

        if($offset == 1) {
            $this->body = $value;
        }

        if($offset == 2) {
            $this->headers = $value;
        }
    }

    /**
     * Method for retro-compatibility with the array access interface inside middleware.
     *
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        if($offset == 0) {
            $this->statusCode = 0;
        }

        if($offset == 1) {
            $this->body = null;
        }

        if($offset == 2) {
            $this->headers = [];
        }
    }

    /**
     * Method for retro-compatibility with the array access interface inside middleware.
     */
    public function count(): int
    {
        return 3;
    }

    /**
     * Create a successful response (200 OK).
     *
     * @param mixed $body
     * @return Response
     */
    public static function success(mixed $body = null): Response {
        return new Response(200, $body, []);
    }

    /**
     * Create a response for resource creation (201 Created).
     *
     * @param mixed $body
     * @return Response
     */
    public static function created(mixed $body = null): Response {
        return new Response(201, $body, []);
    }

    /**
     * Create a redirect response (302 Found).
     *
     * @param string $url
     * @return Response
     */
    public static function redirect(string|null $url = null): Response {
        $headers = $url ? ['Location' => $url] : [];
        return new Response(302, null, $headers);
    }

    /**
     * Create a bad request response (400 Bad Request).
     *
     * @param mixed $body
     * @return Response
     */
    public static function badRequest(mixed $body = null): Response {
        return new Response(400, $body, []);
    }

    /**
     * Create a response for unauthorized (401 Unauthorized).
     *
     * @return Response
     */
    public static function unauthorized(mixed $body = null): Response {
        return new Response(401, $body);
    }

    /**
     * Create a forbidden response (403 Forbidden).
     *
     * @param mixed $body
     * @return Response
     */
    public static function forbidden(mixed $body = null): Response {
        return new Response(403, $body);
    }

    /**
     * Create a response for not found (404 Not Found).
     *
     * @return Response
     */
    public static function notFound(mixed $body = null): Response {
        return new Response(404, $body);
    }

    /**
     * Create an error response (500 Internal Server Error).
     *
     * @param mixed $body
     * @return Response
     */
    public static function error(mixed $body = null): Response {
        return new Response(500, $body, []);
    }

    /**
     * Check if a response has an error status code (>= 400).
     *
     * @param Response|array $response
     * @return bool
     */
    public static function hasError(Response|array $response): bool {
        if($response instanceof Response) {
            return $response->statusCode() >= 400;
        }

        return $response[0] >= 400;
    }
}
