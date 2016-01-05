<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.3.0
 */

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    "driver"    => $app->config->get("db.driver"),
    "host"      => $app->config->get("db.host"),
    "database"  => $app->config->get("db.name"),
    "username"  => $app->config->get("db.username"),
    "password"  => $app->config->get("db.password"),
    "charset"   => $app->config->get("db.charset"),
    "collation" => $app->config->get("db.collation"),
    "prefix"    => $app->config->get("db.prefix")
]);

$capsule->bootEloquent();
