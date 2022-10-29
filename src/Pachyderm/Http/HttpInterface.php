<?php

namespace Pachyderm\Http;

interface HttpInterface
{
    /**
     * Request part
     */
    public function method(): string;
    public function uri(): string;
    public function body(): string|null;


    /**
     * Response part
     */
    public function setStatusCode(int $code): HttpInterface;
    public function setHeader(string $header, string $value): HttpInterface;
    public function setBody(string $body): HttpInterface;
    public function send(): void;
}
