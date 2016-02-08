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

use Slim\Slim;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

use Noodlehaus\Config;

use RandomLib\Factory as RandomLib;

use app\User\User;

use app\Helpers\Hash;
use app\Helpers\GoogleAPI;
use app\BlueAlliance\API;
use app\Teams\Team;
use app\Validation\Validator;

use app\Mail\Mailer;

use app\MiddleWare\BeforeMiddleWare;
use app\MiddleWare\CsrfMiddleWare;

session_cache_limiter("public");
session_start();

require_once("defines.php");

require_once(INC_ROOT . "/vendor/autoload.php");

$mode = json_decode(file_get_contents(INC_ROOT . "/mode.json"));

$app = new Slim([
    "mode" => $mode->mode,
    "view" => new Twig(),
    "templates.path" => INC_ROOT . "/asap-scouting/views",
    "cookies.encrypt" => true,
    "cookies.httponly" => true,
]);

$app->add(new BeforeMiddleware);
$app->add(new CSRFMiddleware);

$app->configureMode($app->config("mode"), function () use ($app) {
    $app->config = Config::load(INC_ROOT . "/asap-scouting/config/{$app->mode}.json");
});

date_default_timezone_set(ini_get("date.timezone") ? ini_get("date.timezone") : $app->config->get("app.timezone"));

require("database.php");
require("filters.php");
require("routes.php");

$app->container->set("user", function () {
    return new User;
});

$app->container->singleton("hash", function () use ($app) {
    return new Hash($app->config);
});

$app->container->singleton("GoogleAPI", function () use ($app) {
    return new GoogleAPI($app);
});

$app->container->singleton("validation", function () use ($app) {
    return new Validator($app->user, $app->hash, $app->auth);
});

$app->container->singleton("tba", function () use ($app) {
    return new API;
});

$app->container->singleton("teams", function () use ($app) {
    return new Team;
});

$app->container->singleton("mail", function () use ($app) {
    $mailer = new PHPMailer;

    $mailer->isSMTP();
    $mailer->Host = $app->config->get("mail.host");
    $mailer->SMTPAuth = $app->config->get("mail.smtp_auth");
    $mailer->SMTPSecure = $app->config->get("mail.smtp_secure");
    $mailer->Port = $app->config->get("mail.port");
    $mailer->Username = $app->config->get("mail.username");
    $mailer->Password = $app->config->get("mail.password");
    $mailer->SMTPDebug = $app->config->get("mail.debug");
    $mailer->isHTML = $app->config->get("mail.html");

    $mailer->setFrom($app->config->get("mail.fromEmail"));

    return new Mailer($app->view, $mailer);
});

$app->container->singleton("randomlib", function () {
    $factory = new RandomLib;
    return $factory->getMediumStrengthGenerator();
});

$view = $app->view();

$view->parserOptions = [
    "debug" => $app->config->get("twig.debug")
];

$view->parserExtensions = [
    new TwigExtension,
    new Twig_Extension_Debug,
];
