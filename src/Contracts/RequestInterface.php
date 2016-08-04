<?php

namespace seregazhuk\SmsIntel\Contracts;

interface RequestInterface
{
    /**
     * Send the request to API
     * @param string $uri
     * @param array $params
     */
    public function exec($uri, $params = []);
}
