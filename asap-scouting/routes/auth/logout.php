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

$app->get( '/logout', $authenticated(), function () use ($app) {
    unset($_SESSION[$app->config->get('auth.session')]);

    if ($app->getCookie($app->config->get('auth.remember'))) {
        $app->auth->removeRememberCredentials();
        $app->deleteCookie($app->config->get('auth.remember'));
    }

    return $app->response->redirect($app->urlFor('home'));

})->name('auth.logout');
