<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Carbon\Carbon;
use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;

$app->get("/passcode", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
	$cookies = Cookies::fromRequest($request);
	if ($cookies->has($this->config->get("services.authentication.cookie"))) {
		$cookie = explode("=", $cookies->get($this->config->get("services.authentication.cookie")));
		
		$cookie = explode("___", $cookie[1]);
		$id = $cookie[0];
		$token = $cookie[1];
		if ($this->user->where("remember_id", "=", $id)->exists()) {
			$user = $this->user->where("remember_id", "=", $id)->first();
			if ($this->hash->hashCheck($user->remember_token, urldecode($token)) &&
				Carbon::parse($user->expires)->gt(Carbon::now())
			) {
				return $response->withRedirect($this->config->get("services.redirect"));
			}
		}
	}

	return $this->view->render($response, "login.twig");
})->setName("login.passcode");

$app->post("/passcode", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $params = $request->getParams();

    $passcode = $params["passcode"];

    if ($this->hash->hashCheck($this->config->get("services.authentication.passcode"), $passcode)) {
    	$id = $this->hash->hash($this->random->generateString(32));
    	$token = $this->hash->password($this->random->generateString(64));
        $this->user->create([
        	"remember_id" => $id,
        	"remember_token" => $token,
        	"expires" => Carbon::now()->addHours(14),
        ]);
        $response = FigResponseCookies::set($response, SetCookie::create($this->config->get("services.authentication.cookie"))
		    ->withValue("{$id}___{$token}")
			->withPath("/")
			->withExpires(Carbon::now()->addHours(14))
		);
        return $response->withRedirect($this->config->get("services.redirect"));
    } else {
        return $this->view->render($response, "login.twig", [
        	"error" => "Invalid Passcode",
        ]);
    }
    
    return $this->view->render($response, "login.twig");
})->setName("login.passcode.post");