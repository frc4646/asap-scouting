<?php

namespace App\Extensions;

class AssetTwigExtension extends \Twig_Extension
{
	protected $structureMode;

	protected $structure = [
		"js" => "/objects/js/{file}",
		"css" => "/objects/css/{file}",
		"img" => "/objects/img/{file}",
	];

	protected $settings = [
		"absolute" => false,
		"baseUrl" => "/objects/{type}/{file}",
		"host" => "https://localhost"
	];

	public function __construct($structure = [], $settings = null)
	{
		if (is_array($structure)) {
			$this->structureMode = "array";
			$this->structure = array_merge($this->structure, $structure);
		} elseif (!is_null($structure)) {
			$this->structure = $structure;
		}

		if (is_array($settings) && !empty($settings)) {
			$this->settings = array_merge($this->settings, $settings);
		}
	}

	public function getName()
	{
		return "asset-extension";
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction("asset", [$this, "makeAssetUrl"]),
		];
	}

	public function makeAssetUrl($filename, $filetype, $subfolder = null)
	{
		if (!is_null($subfolder)) {
			$subfolder = rtrim($subfolder, "/");
			$subfolder = ltrim($subfolder, "/");
		}

		switch ($this->structureMode) {
			case "array":
				return $this->makeAssetUrlFromArray($filename, $filetype, $subfolder);
				break;
			default:
				return $this->makeAssetUrlFromString($filename, $filetype, $subfolder);
				break;
		}
	}

	protected function makeAssetUrlFromArray($filename, $filetype, $subfolder)
	{
		if (array_key_exists($filetype, $this->structure)) {
			$url = $this->structure[$filetype];

			if (!is_null($subfolder)) {
				$url = str_replace("{file}", $subfolder . "/{file}", $url);
			}

			if ($this->settings["absolute"]) {
				$url = $this->settings["host"] . $url;
			}

			return str_replace("{file}", $filename, $url);
		}
	}

	protected function makeAssetUrlFromString($filename, $filetype, $subfolder)
	{
		$url = $this->settings["baseUrl"];

		if (!is_null($subfolder)) {
			$url = str_replace("{file}", $subfolder . "/{file}", $url);
		}

		if ($this->settings["absolute"]) {
			$url = $this->settings["host"] . $url;
		}

		return str_replace("{file}", $filename, $url);
	}
}