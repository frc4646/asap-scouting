<?php

namespace App\BlueAlliance;

use GuzzleHttp\Client;

class Connection
{
	protected $connection;

	protected $baseUrl = "https://www.thebluealliance.com/api/v2/";

	protected $client;

	protected $ssl = true;

	protected $id = "frc4646:scouting-system:v01";

	public function __construct(array $config = [])
	{
		if (isset($config["baseUrl"])) {
			$this->baseUrl = $config["baseUrl"];
		}

		if (isset($config["id"])) {
			$this->id = $config["id"];
		}

		if (isset($config["ssl"]) && ($config["ssl"] === "off" || !$config["ssl"])) {
			$isSchemeCorrect = parse_url($this->baseUrl, PHP_URL_SCHEME) == "http";
			if (!$isSchemeCorrect) {
				$url = explode("https", $this->baseUrl)[1];
				$this->baseUrl = "http" . $url;
			}
		}

		if (isset($config["ssl"]) && ($config["ssl"] === "on" || $config["ssl"])) {
			$isSchemeCorrect = parse_url($this->baseUrl, PHP_URL_SCHEME) == "https";
			if (!$isSchemeCorrect) {
				$url = explode("http", $this->baseUrl)[1];
				$this->baseUrl = "https" . $url;
			}
		}
	}

	public function start(Client $client = null)
	{
		if (is_null($client)) {
			$this->client = new Client([
				"base_uri" => $this->baseUrl,
				//"timeout" => 5,
				"allow_redirects" => false,
				//"connect_timeout" => 10,
				"decode_content" => true,
				"headers" => [
					"X-TBA-App-Id" => $this->id,
				],
			]);
		} else {
			$this->client = $client;
		}

		return $this->client;
	}

	public function getClient()
	{
		return $this->client;
	}
}