<?php

namespace CJSDevelopment\config;

class Configuration
{
    private $data = [
        'version' => '1.1',
        'api_url' => 'https://www.qantanipayments.com/api/',
        ];

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
    }
}
