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
    public function setHeader(string $header): HttpInterface;
    public function setBody(string $body): HttpInterface;
    public function send(): void;
}
