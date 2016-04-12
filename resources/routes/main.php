<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;

$app->get("/", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	/*$regional = isset($_SESSION["regional"]) ? $_SESSION["regional"] : null;
	return $response->write($regional);*/
	return $this->view->render($response, "home.twig", [
		"matches" => $this->matches->all(),
	]);
})->setName("home");

$app->get("/a", function ($r, $response) {
	$x = new \App\BlueAlliance\Connection;

	$c = $x->start();

	$k = $c->getAsync("event/2010sc");

	$k->then(
	    function (ResponseInterface $res) use ($response) {
	        $response->write((string) $res->getBody());
	    },
	    function (RequestException $e) use ($response) {
	        $response->write(var_dump($e));
	    }
	)->wait();

	$response = $response->withHeader('Content-type', 'application/json');

	return $response/*->write(var_dump($x))*/;
});

$app->get("/prefs/regional[/{regional}]", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	if ($this->db->schema()->hasTable($this->matches->getPrefix() . $args["regional"])) {
		$_SESSION["regional"] = $args["regional"];
		return $response->withJson(["regional" => $_SESSION["regional"]], 200);
	} else {
		return $response->withJson(["error" => "Match Table does not exists."], 500);
	}
})->setName("set.regional");

$app->get("/test/teams", function ($request, $response, $args) {
	/*$_SESSION["ar"] = "0a";
	die(var_dump($_SESSION["ar"] ?: null));*/
	$a = $this->matches->where("id", "=", 1)->first()->teams()->first()->name;

	/*foreach ($this->matches->where("id", "=", 1)->first()->teams()->get() as $h) {
		$a[] = $h->toArray();
	}*/
	return $response->write(var_dump($a));
});

$app->get("/test/matches", function ($request, $response, $args) {
	$a = $this->teams->where("id", "=", 4)->first()->matches()->all();
	$w = [];

	foreach($a as $match) {
		$w[] = $match->toArray();
	}

	return $response->write(var_dump($w));
});

$app->get("/test/match", function ($request, $response, $args) {
	$x = $this->matches->create($this->matches->where("id", "=", 1)->first()->toArray());

	return $response->write(var_dump($x->toArray()));
});

$app->get("/test/addteam/{team}", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	//return $response->withJson([0 => 5, 6 => 1]);
	$x = $this->teams->create([
		"name" => $this->random->generateString(16),
		"number" => $args["team"],
		"auto_reach" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"auto_cross" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"auto_high" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"auto_low" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_high" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_low" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_portcullis" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_cheval_de_frise" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_moat" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_ramparts" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_drawbridge" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_sally_port" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_rock_wall" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_rough_terrain" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_low_bar" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_scale" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
		"tele_challenge" => json_encode([0 => $this->random->generateInt(1, 15), 6 => 1]),
	]);

	//$response->write(var_dump($x->toArray()));
	return $response->withJson($x->toArray());
});