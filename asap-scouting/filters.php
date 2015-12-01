<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.1.0
 */

$authenticationCheck = function ($required) use ($app) {
    return function () use ($required, $app) {
        if ((!$app->auth && $required) || ($app->auth && !$required)) {
            $app->redirect($app->urlFor('home'));
        }
    };
};

$authenticated = function () use ($authenticationCheck) {
    return $authenticationCheck(true);
};

$guest = function () use ($authenticationCheck) {
    return $authenticationCheck(false);
};

$admin = function () use ($app) {
    return function () use ($app) {
        if (!$app->auth || !$app->auth->isAdmin()) {
            $app->notFound();
        }
    };
};
