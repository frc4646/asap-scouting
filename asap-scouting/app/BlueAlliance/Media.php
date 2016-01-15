<?php

namespace app\BlueAlliance;

use GuzzleHttp\Client;

use GuzzleHttp\Psr7\Request;

class Media
{
    protected $client;

    protected $api;

    protected $year;

    protected $team;

    public function __construct(Client $client, array $details, API $api)
    {
        $this->client = $client;
        $this->api = $api;

        if (array_key_exists("team", $details)) {
            $this->team = $details["team"];
        }

        if (array_key_exists("year", $details)) {
            $this->year = $details["year"];
        }
    }

    public function getImages()
    {
        return (array) [$this, (string) $this->client->get("team/frc{$this->team}/{$this->year}/media", $this->api->headers)->getBody()];
    }

    public function getYoutube()
    {
        return new Images\Youtube($this->getImages()[1]);
    }
}
