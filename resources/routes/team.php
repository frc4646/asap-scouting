<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\HttpCache\Cache;
use App\Messages\Validation;
use Illuminate\Support\Collection;

$app->group("/team/{team}", function () {
	$this->get("/", function (ServerRequestInterface $request, ResponseInterface $response, $args) {

		$v = $this->validate->make([
			"team" => $args["team"],
		], [
			"team" => "required|integer|exists:teams,number"
		], array_merge(Validation::$messages, [
			"exists" => "That team does not exist in the database",
		]));

		if ($v->passes()) {
			return $response->withJson(
				$this->teams->where("number", "=", $args["team"])
					->first()
					->toArray()
			);
		}

		return $response->withJson($v->errors()->all());
	});
})->add(new Cache("private", 0, true));