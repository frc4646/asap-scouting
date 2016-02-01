<?php

use app\Teams\Team;

$app->group("/teams", function () use ($app) {
    $app->get("/:team/create", function ($team) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
        ]);
        
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
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
        ]);
    
        if ($v->passes()) {
            if ($app->teams->where("team_id", $team)->exists()) {
                echo json_encode((array) json_decode($app->teams->where("team_id", $team)->first()->details));
                //echo json_encode($app->reponse->finalize());
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
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;
        $post = $app->request->post();

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
            "set" => [$item, "required|alnum"],
            "item" => [$post["item"], "required|alnumDash"],
            "value" => [$post["val"], "required"],
        ]);

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
    
    $app->get("/:team/chphotos/:year", function ($team, $year) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;
        
        $v->validate([
            "team" => [$team, "required|int"],
            "year" => [$year, "required|int"],
        ]);
        if ($v->passes()) {
            $array = $app->tba->getMedia(["team" => $team, "year" => $year])->getImages()[0]->getYoutube()->get();
            $array["base_uri"] = [
                "cdphotothread" => "http://www.chiefdelphi.com/media/img/",
                "youtube" => "https://youtube.com/watch?",
            ];
            return $app->response->write(json_encode($array));
        }
        return $app->response->write(json_encode($v->errors()));
    });
});
