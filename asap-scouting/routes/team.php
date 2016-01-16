<?php

use app\Teams\Team;

$app->group("/teams", function () use ($app) {
    $app->response->headers->set("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
    $app->response->headers->set("Cache-Control", "post-check=0, pre-check=0");
    $app->response->headers->set("Pragma", "no-cache");

    $app->get("/:team/create", function ($team) use ($app) {
        $v = $app->validation;

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
        ]);

        $app->response->headers->set("Content-Type", "application/json");
        if ($v->passes()) {
            if (!$app->teams->where("team_id", $team)->exists()) {
                $newTeam = $app->teams->create([
                    "team_id" => $team,
                    "details" => json_encode(Team::$defaults),
                ]);

                echo json_encode(["success" => true, "result" => $newTeam]);
                return;
            } else {
                echo json_encode(["success" => true, "errorMessage" => "Team with id $team already exists!"]);
                return;
            }
        }
        echo json_encode(["success" => false, $v->errors()]);
        return;
    })->name("team.create");

    $app->get("/:team/get", function ($team) use ($app) {
        $v = $app->validation;

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
        ]);

        $app->response->headers->set("Content-Type", "application/json");
        if ($v->passes()) {
            if ($app->teams->where("team_id", $team)->exists()) {
                echo json_encode((array) json_decode($app->teams->where("team_id", $team)->first()->details));
                return;
            } else {
                echo json_encode(["success" => false, "errorMessage" => "Team with id $team does not exist!"]);
                return;
            }
        }
        echo json_encode($v->errors());
        return;
    })->name("team.get");

    $app->post("/:team/set/:item", function ($team, $item) use ($app) {
        $v = $app->validation;
        $post = $app->request->post();

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
            "set" => [$item, "required|alnum"],
            "item" => [$post["item"], "required|alnumDash"],
            "value" => [$post["val"], "required"],
        ]);

        $app->response->headers->set("Content-Type", "application/json");
        if ($v->passes()) {
            if ($app->teams->where("team_id", $team)->exists()) {
                $curr = $app->teams->where("team_id", $team)->first();
                $currDetails = (array) json_decode($curr->details, true);

                $currDetails["runOnce"] = false;

                if (isset($currDetails[$item])) {
                    $currDetails[$item][$post["item"]] = $post["val"];

                    $curr->update([
                        "details" => json_encode($currDetails),
                    ]);

                    $curr->save();
                    echo json_encode($currDetails);
                } else {
                    echo json_encode(["errorMessage" => "Array Item doesn't exist!"]);
                    return;
                }
                return;
            }
            echo json_encode(["errorMessage" => "Team Not Found"]);
            return;
        }

        echo json_encode($v->errors());
        return;
    })->name("team.set");
});
