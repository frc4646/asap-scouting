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

$app->get('/', function () use ($app) {
    $app->render('home.twig');
})->name('home');

$app->get("/app", function () use ($app) {
    $listFeed = $app->GoogleAPI->getSheet()->getListFeed();
    $table = [];

    foreach ($listFeed->getEntries() as $entry => $val) {
        $table[$entry] = $val->getValues();
    }

    $app->render("partials/tableOut.twig", [
        "entries" => $table,
    ]);
})->name("app.datatable");
