<?php

use Slim\Container;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Slim\Flash\Messages;

use Illuminate\Database\Capsule\Manager as Capsule;

use RandomLib\Factory as RandomLib;

use Noodlehaus\Config;

use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

use App\Middleware\CSRF;
use App\Middleware\Before;

use App\Hash\Hash;

use App\Authentication\Auth;

use App\Models\User;
use App\Models\Teams;
use App\Models\Matches;

use App\Extensions\ConfigTwigExtension;
use App\Extensions\AssetTwigExtension;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Bramus\Monolog\Formatter\ColoredLineFormatter;

use Illuminate\Container\Container as LaraContainer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory as Validator;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\Cookies;

session_start();

require_once("../vendor/autoload.php");

const APP_VERSION = "3.0.0";

$config = [
    "settings" => [
        "displayErrorDetails" => true,
        "determineRouteBeforeAppMiddleware" => true,
        "debug" => true,
        "whoops.editor" => "sublime",
    ],
];

$container = new Container($config);

$container["config"] = function ($c) {
    return new Config("../config/app.php");
};

$container["view"] = function ($c) {
    $view = new Twig("../resources/views", [
        //"cache" => "../resources/cache/views",
    ]);
    
    $view->addExtension(new TwigExtension(
        $c["router"],
        $c["config"]->get("url")
    ));
    
    $view->addExtension(new ConfigTwigExtension(
        $c["config"]
    ));

    $view->addExtension(new AssetTwigExtension([], [
        "absolute" => true,
        "host" => $c["config"]->get("url"),
    ]));

    $view->addExtension(new Twig_Extension_Debug());

    $view->getEnvironment()->enableDebug();
    
    return $view;
};

$container["user"] = function ($c) {
    return new User;
};

$container["teams"] = function ($c) {
    return new Teams;
};

$container["matches"] = function ($c) {
    return new Matches($c["config"]->get("services.matches.default"));
};

$container["csrf"] = function ($c) {
    return new Guard;
};

$container["hash"] = function ($c) {
    return new Hash($c["config"]);
};

$container["db"] = function ($c) {
    $capsule = new Capsule;

    $config = $c["config"];

    $driver = $c["config"]->get("db.driver");

    $capsule->addConnection([
        "driver"    => $config->get("db.driver"),
        "host"      => $config->get("db.$driver.host"),
        "database"  => $config->get("db.$driver.dbname"),
        "username"  => $config->get("db.$driver.username"),
        "password"  => $config->get("db.$driver.password"),
        "prefix"    => $config->get("db.$driver.prefix"),
        "charset"    => $config->get("db.$driver.charset"),
        "collation"    => $config->get("db.$driver.collation"),
    ]);

    return $capsule;
};

$container["log"] = function ($c) {
    $log = new Logger("ASAP-Scouting");
    $handler = new StreamHandler("../logs/" . date("Y-m-d") . ".log", Logger::DEBUG);
    $dateFormat = "Y n j, g:i a";
    $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
    $formatter = new LineFormatter($output, $dateFormat);
    $handler->setFormatter($formatter);
    $handler->setFormatter(new ColoredLineFormatter());
    $log->pushHandler($handler);

    return $log;
};

$container["auth"] = function ($c) {
    return new Auth($c);
};

$container["random"] = function ($c) {
    return (new RandomLib)->getMediumStrengthGenerator();
};

$container["validate"] = function ($c) {
    $loader = new FileLoader(new Filesystem, '../lang');
    $translator = new Translator($loader, 'en');
    $presence = new DatabasePresenceVerifier($c["db"]->getDatabaseManager());
    $validation = new Validator($translator, new LaraContainer);
    $validation->setPresenceVerifier($presence);

    return $validation;
};

$container["flash"] = function ($c) {
    return new Messages;
};

$app = new App($container);

$container = $app->getContainer();

$container["db"]->setAsGlobal();
$container["db"]->bootEloquent();

$app->add(new WhoopsMiddleware);

$app->add("auth");

$app->add(new CSRF($container["csrf"], $container["view"]));

$app->add(new Before($container["view"], $container["log"]));

$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($container) {
    $db = $container["db"];
    $match = $container["matches"];
    $config = $container["config"];

    $cookies = Cookies::fromRequest($request);

    if ($cookies->has("regional")) {
        $value = $cookies->get("regional")->getValue() ?: $config->get("services.matches.default");
        if ($db->schema()->hasTable($match->getPrefix() . $value)) {
            $match->setTable($match->getPrefix() . $value);
            $_SESSION["regional"] = $value;
            return $next($request, $response);
        }  
    }

    $response = FigResponseCookies::set($response, SetCookie::create("regional")
        ->withValue($config->get("services.matches.default"))
        ->withHttpOnly(true)
        ->withPath("/")
        ->rememberForever()
    );

    $_SESSION["regional"] = $config->get("services.matches.default");

    return $next($request, $response);
});

$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("../resources/routes"));
foreach ($iterator as $file) {
    $fname = $file->getFilename();
    if (preg_match("%\.php$%", $fname)) {
        require_once($file->getPathname());
    }
}