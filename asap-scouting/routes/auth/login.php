<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP)
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2016 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     v1-beta
 */

use Carbon\Carbon;

$app->get("/login", $guest(), function () use ($app) {
    $app->render("auth/login.twig");
})->name("auth.login");

$app->post("/login", $guest(), function () use ($app) {
    $request = $app->request;

    $user = $request->post("user");
    $password = $request->post("password");
    $remember = $request->post("remember");

    $v = $app->validation;

    $v->validate([
        "user" => [$user, "required|alnumDashSpc"],
        "password" => [$password, "required|alnumDashSpc"],
    ]);

    if ($v->passes()) {
        $user = $app->user
            ->where("active", true)
            ->where(function ($query) use ($user) {
                    return $query->where("email", $user)
                                 ->orWhere("username", $user);
            })
            ->first();

        if ($user && $app->hash->passwordCheck($password, $user->password)) {
            $_SESSION[$app->config->get("auth.session")] = $user->id;

            if ($remember === "on") {
                $rememberIdentifier = $app->randomlib->generateString(128);
                $rememberToken = $app->randomlib->generateString(128);

                $user->updateRememberCredentials(
                    $rememberIdentifier,
                    $app->hash->hash($rememberToken)
                );

                $create = $app->setCookie(
                    $app->config->get("auth.remember"),
                    "{$rememberIdentifier}___{$rememberToken}",
                    Carbon::parse("+1 month")->timestamp,
                    null,
                    null,
                    true,
                    true
                );
            }

            if ($app->request->isAjax()) {
                echo json_encode([
                    "success" => true,
                    "url" => $app->urlFor("home"),
                ]);
                return;
            }
            return $app->response->redirect($app->urlFor("home"));

        } else {
            if ($app->request->isAjax()) {
                echo json_encode([
                    "success"   => false,
                    "errors"    => $v->errors(),
                    "url"       => $app->urlFor("auth.login"),
                ]);
                return;
            }
            return $app->response->redirect($app->urlFor("auth.login"));
        }
    }

    $app->render("auth/login.twig", [
        "errors" => $v->errors(),
        "request" => $request,
    ]);
})->name("auth.login.post");
