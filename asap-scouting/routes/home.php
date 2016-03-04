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

$app->get("/", function () use ($app) {
    $app->render("home.twig");
})->name("home");

$app->get("/app", function () use ($app) {
    $app->response->headers->set("Content-Type", "text/html");
    header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
    $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    $app->response->headers->set("Pragma", "no-cache");
    $app->expires("-1 week");
    $app->lastModified(strtotime("-1 week"));
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
    $app->response->headers->set("Content-Type", "application/json");
    $listEntries = $app->GoogleAPI->getSheet("Quals")->getListFeed()->getEntries();

    $v = $app->validation;
    $data_id = $app->request->post()["data_id"];

    $v->validate([
        "id" => [$data_id, "required|int"]
    ]);

    $app->response->headers->set("Content-Type", "application/json");

    if ($v->passes()) {
        echo json_encode($listEntries[$data_id - 1]->getValues());
    } else {
        echo json_encode([
            "success" => false,
            "errors" => $v->errors()
        ]);
    }
})->name("app.data.get");

$app->post("/app/set", function () use ($app) {
    $app->response->headers->set("Content-Type", "application/json");
    $v = $app->validation;

    $rowID = $app->request->post()["rowID"];
    $column = $app->request->post()["column"];
    $newValue = $app->request->post()["newValue"];
    
    if (!isset($newValue)) {
        return $app->response->write(json_encode([
            "success" => false,
            "errors" => "newValue is required",
        ]));
    }
    
    $v->validate([
       "newValue" => [$newValue, "int"],
       "rowID" => [$rowID, "required|int"],
       "column" => [$column, "required|alnum"],
    ]);

    if ($v->passes()) {
        $set = $app->GoogleAPI->setValue($rowID, $column, $newValue);
        if ($set) {
            echo json_encode([
                "success" => true,
                "item" => $set,
                "orginal" => [
                    "newValue" => $newValue,
                    "rowID" => $rowID,
                    "column" => $column,
                ],
            ]);
            return;
        } else {
            echo json_encode([
                "success" => false,
                "item" => $set,
                "orginal" => [
                    "newValue" => $newValue,
                    "rowID" => $rowID,
                    "column" => $column,
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

$app->get("/pit", function () use ($app) {
    return $app->render("pit.twig");
})->name("home.pit");

$app->get("/app/pit", function () use ($app) {
    $app->response->headers->set("Content-Type", "text/html");
    header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
    $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    $app->response->headers->set("Pragma", "no-cache");
    $app->expires("-1 week");
    $app->lastModified(strtotime("-1 week"));
    $teams = $app->teams->all();
    $out = [];

    foreach ($teams as $team) {
        $out[] = ["id" => $team->team_id, "details" => json_decode($team->details, true)];
    }

    $app->render("partials/pitOut.twig", [
        "teams" => $out,
    ]);
})->name("home.pit.html");

$app->get("/data", function () use ($app) {
    $teams = $app->teams->get();
    $out = [];

    foreach($teams as $team) {
        $out[] = ["team_id" => $team->team_id, "details" => json_decode($team->details, true)];
    }

    return $app->render("teamdata.twig", [
        "teams" => $out,
        "fields" => $app->teams->getSortable()
    ]);
})->name("home.team.data");

$app->get("/pre", function () use ($app) {
    return $app->render("pre.twig");
})->name("home.pre");

$app->get("/app/pre", function () use ($app) {
    $app->response->headers->set("Content-Type", "text/html");
    header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
    $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    $app->response->headers->set("Pragma", "no-cache");
    $app->expires("-1 week");
    $app->lastModified(strtotime("-1 week"));
    $out = [];

    $teams = $app->GoogleAPI->getSheet("PreScouting")->getListFeed()->getEntries();

    foreach ($teams as $team) {
        $e = $team->getValues();
        $out[] = ["id" => $e["teamnumbers"], "details" => ["name" => $e["teamnames"]]];
    }

    $app->render("partials/pitOut.twig", [
        "teams" => $out,
    ]);
})->name("home.pre.html");

$app->get("/data/sort", function () use ($app) {
    $app->response->headers->set("Content-Type", "application/json");
    header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
    $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    $app->response->headers->set("Pragma", "no-cache");
    $app->expires("-1 week");
    $app->lastModified(strtotime("-1 week"));
    return $app->response->write(json_encode($app->teams->orderBy("team_id")->get()));
});

$app->get("/hash", function () use ($app) {
    return $app->response->write(json_encode(base64_encode($app->randomlib->generateString(32))));
});