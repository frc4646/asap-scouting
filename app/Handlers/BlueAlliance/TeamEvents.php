<?php

namespace App\BlueAlliance;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Exception\RequestException;

use App\BlueAlliance\Connection;

use Illuminate\Support\Collection;

class TeamEvents
{
	protected $team;

	protected $year;

	protected $client;

	protected $urls = [
		"events" => [
			"list" => "team/frc%d/%d/events"
		],
	];

	public function __construct($team = null, $year = null)
	{
		if (is_int($team)) {
			$this->team = $team;
		}

		if (is_int($year)) {
			$this->year = $year;
		}

		$this->client = (new Connection)->start();
	}

	public function getEventCodes()
	{
		$async = $this->client->getAsync(sprintf($this->urls["events"]["list"], $this->team, $this->year));

		$output = (string) $async->wait()->getBody();

		$output = json_decode($output, true);

		$events = [];

		foreach ($output as $event) {
			$events[] = new Event($event["key"], $this->client);
		}

		$events = new Collection($events);

		return $events;
	}

	public function setTeam($team)
	{
		if (is_int($team)) {
			$this->team = $team;
		}

		return $this;
	}

	public function getTeam()
	{
		return $this->team;
	}

	public function setYear($year)
	{
		if (is_int($year)) {
			$this->year = $year;
		}

		return $this;
	}

	public function getYear()
	{
		return $this->year;
	}
}