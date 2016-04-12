<?php

namespace App\Extensions;

class ConfigTwigExtension extends \Twig_Extension
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getName()
    {
        return "config-extension";
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("config", [$this, "config"]),
        ];
    }

    public function config($item)
    {
        return $this->config->get($item);
    }
}
