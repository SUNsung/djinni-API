<?php

namespace djinni;

class reqObj{
    public int $code;
    public string $contentType;
    public string $scheme;
    public string $ip;
    public int $port;

    public string $body;
    public string $url;
    public string $redirectUrl;

    public function __construct(){}
}