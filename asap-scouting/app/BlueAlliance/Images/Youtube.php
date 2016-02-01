<?php

namespace app\BlueAlliance\Images;

use Exception;

class Youtube implements ImageInterface
{
    protected $api;

    public function __construct($api)
    {
        if (!is_string($api)) {
            throw new Exception("Argument 1 passed to the __construct() must be a String");
        }

        $this->api = $api;
    }

    public function get()
    {
        $var = [];

        foreach (json_decode($this->api, true) as $key => $value) {
            $var[$value["type"]] = ["details" => $value["details"], "key" => $value["foreign_key"]];
        }

        return $var;
    }
}
