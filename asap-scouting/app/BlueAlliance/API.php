<?php

namespace app\BlueAlliance;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;

class API
{
    public $apiBase = "www.thebluealliance.com/api/v2/";

    protected $httpClient;

    public $headers = [
        "allow_redirects" => [
            "strict" => false,
            "referer" => true,
            "protocols" => "https",
            "track_redirects" => false,
        ],
        "headers" => [
            "User-Agent" => "PHP GuzzleHttp/Curl Mozzila",
            "Accept" => "application/json",
            "X-TBA-App-Id" => "frc4646:scouting-system:v01",
        ]
    ];

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => $this->apiBase,
            "timeout"  => 2.0,
            "handler" => HandlerStack::create(new CurlHandler()),
        ]);
    }

    public function getMedia(array $details)
    {
        return new Media($this->client, $details, $this);
    }
}
