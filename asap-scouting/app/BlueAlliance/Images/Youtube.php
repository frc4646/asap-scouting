<?php

namespace app\BlueAlliance\Images;

use Exception;

class Youtube implements ImageInterface
{
    protected $api;

    public function __construct($api)
    {
        if (!is_string($api)) {
            throw new Exception("Argument 1 passed to app\BlueAlliance\Images\Youtube::__construct() must be an instance of app\BlueAlliance\Images\String");
        }

        $this->api = $api;
    }

    public function get()
    {
        /*$key = $key = array_search("youtube", array_column(json_decode($this->api), "type"));
        die(var_dump($key));*/
        $var = [];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator(json_decode($this->api)));

        foreach ($iterator as $key => $value) {
            var_dump([$key, $value]);
            /*if ($value == "youtube") {
                die("Youget here");
            }*/
            for($i = $iterator->getDepth() - 1; $i >= 0; $i--){
                //add each parent key
                var_dump($key, $iterator->getSubIterator($i)->key());
            }
        }

        return (array) json_decode($this->api/*["youtube"]*/);
    }
}
