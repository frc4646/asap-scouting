<?php

namespace App\BlueAlliance;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Exception\RequestException;

use App\BlueAlliance\Connection;

class Event
{
	protected $event;

	protected $client;

	protected $urls = [
		"base" => "event/%s",
		"events" => [
			"matches" => "/matches",
		],
	];	

	public function __construct($event, $client)
	{
		if (!is_string($event)) {
			throw new \RuntimeException("Event Code must be a string. Type " . gettype($event) . " given.");
		}

		if (!is_a($client, "GuzzleHttp\Client")) {
			throw new \RuntimeException("Event Client must be type GuzzleHttp\Client. Type " . gettype($event) . " given.");
		}

		$this->event = $event;

		$this->client = $client;
	}

	public function getDetails()
	{
		$async = $this->client->getAsync(sprintf($this->urls["base"], $this->event));

		$output = (string) $async->wait()->getBody();

		$output = json_decode($output, true);

		return $output;
	}

	public function getMatches()
	{
		$base = 

		$async = $this->client->getAsync(sprintf($this->urls["base"], $this->event) . $this->urls["events"]["matches"]);

		$output = (string) $async->wait()->getBody();

		$output = json_decode($output, true);

		return $output;
	}
}