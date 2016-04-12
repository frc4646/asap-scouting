<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Authentication\Roles\Admin;
use Illuminate\Database\Capsule\Manager as Capsule;

$app->group("/admin", function () {
	$this->get("/", function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$query = Capsule::select(Capsule::raw("SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = SCHEMA()"));		
		$results = [];

		foreach($query as $result) {
			if (0 === strpos($result->name, "matches_")) {
				$results[] = $result->name;
			}
		}

		return $this->view->render($response, "admin/home.twig", [
			"tables" => $results,
		]);
	});

	$this->post("/", function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$team = new \App\BlueAlliance\TeamEvents(4646, 2016);

		$x = $team->getEventCodes();

		//$x = $team->getEventByCode("124");

		$g = [];

		foreach ($x as $v) {
			$g[] = $v->getMatches();
		}

		return $response->write(var_dump($g));
	});
})->add(new Admin($app));