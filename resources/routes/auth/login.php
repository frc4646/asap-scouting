<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Authentication\Roles\Authenticated;

$app->get("/login", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    return $this->view->render($response, "auth/login.twig");
})->setName("auth.login.get");

$app->post("/login", function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $params = $request->getParams();

    $user = $params["user"];
    $password = $params["password"];

    $v = $this->validate->make([
        "username" => $user,
        "password" => $password,
    ], [
        "username" => "required|alpha_dash",
        "password" => "required|alpha_dash"
    ]);

    if ($v->passes()) {
        $user = $this->user
            ->where("active", true)
            ->where(function ($query) use ($user) {
                    return $query->where("email", $user)
                                 ->orWhere("username", $user);
            })
            ->first();

        if ($user && $this->hash->passwordCheck($password, $user->password)) {
            $_SESSION[$this->config->get("services.authentication.session")] = $user->id;
            $response->write(json_encode(["success" => true]));
            return $response->withRedirect($this->router->pathFor("home"));
        }
        return $this->view->render($response, "auth/login.twig", [
            "error" => true,
        ]);
    } else {
        return $this->view->render($response, "auth/login.twig", [
            "error" => true,
        ]);
    }
    
    return $response;
})->setName("auth.login.post");

$app->get("/logout", function ($request, $response, $args) {
    return $this->view->render($response, "auth/logout.twig");
})->setName("auth.logout.get")->add(new Authenticated($app));

$app->post("/logout", function ($request, $response, $args) {
    unset($_SESSION[$this->config->get("services.authentication.session")]);
    return $response->withRedirect($this->router->pathFor("home"));
})->setName("auth.logout.post")->add(new Authenticated($app));