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

$app->post("/app/get", function () use ($app) {
    $listEntries = $app->GoogleAPI->getSheet("Quals")->getListFeed()->getEntries();

    $v = $app->validation;

    $v->validate([
        "id" => [$app->request->post()["data_id"], "required|int"]
    ]);

    $app->response->headers->set("Content-Type", "application/json");

    if ($v->passes()) {
        echo json_encode($listEntries[$app->request->post()["data_id"] - 1]->getValues());
    } else {
        echo json_encode([
            "success" => false,
            "errors" => $v->errors()
        ]);
    }
})->name("app.data.get");

$app->post("/app/set", function () use ($app) {
    $v = $app->validation;

    $v->validate([
       "newValue" => [$app->request->post()["newValue"], "required|int"],
       "rowID" => [$app->request->post()["rowID"], "required|int"],
       "column" => [$app->request->post()["column"], "required|alnum"],
    ]);

    if ($v->passes()) {
        if ($app->GoogleAPI->setValue($app->request->post()["rowID"], $app->request->post()["column"], $app->request->post()["newValue"])) {
            echo json_encode([
                "success" => true,
                "item" => $app->GoogleAPI->setValue($app->request->post()["rowID"], $app->request->post()["column"], $app->request->post()["newValue"]),
                "orginal" => [
                    "newValue" => $app->request->post()["newValue"],
                    "rowID" => $app->request->post()["rowID"],
                    "column" => $app->request->post()["column"],
                ],
            ]);
            return;
        } else {
            echo json_encode([
                "success" => false,
                "item" => $app->GoogleAPI->setValue($app->request->post()["rowID"], $app->request->post()["column"], $app->request->post()["newValue"]),
                "orginal" => [
                    "newValue" => $app->request->post()["newValue"],
                    "rowID" => $app->request->post()["rowID"],
                    "column" => $app->request->post()["column"],
                ],
            ]);
            return;
        }
    } else {
        echo json_encode([
            "success" => false,
            "errors" => $v->errors(),
        ]);
    }
})->name("app.data.set");
