<?php

namespace Pachyderm\Http;

interface HttpInterface
{
    /**
     * Request part
     */
    public function method(): string;
    public function uri(): string;
    public function path(): string;
    public function body(): string|null;
    public function bodyParams(): array|null;


    /**
     * Response part
     */
    public function setStatusCode(int $code): HttpInterface;
    public function addHeaders(array $headers): HttpInterface;
    public function setHeader(string $header, string $value): HttpInterface;
    public function setBody(string $body): HttpInterface;
    public function send(): void;
}
