<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

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
