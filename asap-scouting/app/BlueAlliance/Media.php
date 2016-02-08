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
        return (array) [
                        $this,
                        $this->client
                          ->get(
                              "team/frc{$this->team}/{$this->year}/media",
                              $this->api->headers
                          )->getBody()->getContents()
                       ];
    }

    public function getYoutube()
    {
        //die(var_dump($this->client->get("team/frc{$this->team}/{$this->year}/media", $this->api->headers)->getBody()->getContents()));
        return new Images\Youtube($this->getImages()[1]);
    }
}
