<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Authentication\Roles\Admin;
use Illuminate\Database\Capsule\Manager as Capsule;

$app->group("/admin", function () {
	$this->get("/", function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
		$query = Capsule::select(Capsule::raw("SELECT TABLE_NAME AS a FROM information_schema.tables WHERE table_schema = DATABASE()"));		

		foreach($query as $x) {
			if (0 === strpos($x->a, "matches_")) {
				var_dump($x->a);
			}
		}

		return $this->view->render($response, "admin/home.twig", [
			"tables" => /*json_decode(json_encode(*/$query/*)/*, true*/,
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