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

use app\Teams\Team;
use app\Helpers\GoogleAPI;

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
                    "nonce" => base64_encode($app->randomlib->generateString(32)),
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
                $details = json_decode($app->teams->where("team_id", $team)->first()->details, true);
                $details["nonce"] = $app->teams->where("team_id", $team)->first()->nonce;
                return $app->response->write(json_encode($details));
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

        if ($v->passes()) {
            if ($app->teams->where("team_id", $team)->exists()) {
                $curr = $app->teams->where("team_id", $team)->first();
                $currDetails = (array) json_decode($curr->details, true);

                $currDetails["runOnce"] = false;

                if (isset($currDetails[$item])) {
                    $currDetails[$item][$post["item"]] = htmlentities($post["val"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");

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
    })->name("team.get.chphotos");

    $app->get("/:team/remove", function ($team) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;
        
        $v->validate([
            "team" => [$team, "required|int"],
        ]);

        if ($v->passes()) {
            $teaminf = $app->teams->where("team_id", "=", $team)->first();
            $teaminf->delete();
            return $app->response->write(json_encode(["success" => true, "orgin" => $teaminf]));
        }
        return $app->response->write(json_encode(["errors" => $v->errors()->all()]));
    })->name("team.remove");

    $app->get("/:team/matches", function ($team) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $v = $app->validation;
        $listEntries = $app->GoogleAPI->getSheet("Quals")->getListFeed()->getEntries();
        $list = [];
        $out = [];
        $scores = [
            "scores" => [0],
            "min" => 0,
            "max" => 0,
        ];
        
        $v->validate([
            "team" => [$team, "required|int"],
        ]);

        if ($v->passes()) {
            foreach ($listEntries as $key) {
                $list[] = $key->getValues();
            }

            foreach ($list as $match) {
                $allowed = ["red1", "red2", "red3", "blue1", "blue2", "blue3"];
                $searchString = array_filter(
                    $match,
                    function ($key) use ($allowed) {
                        return in_array($key, $allowed);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $search = array_search($team, $searchString);
                if ($search) {
                    $out[] = [$match, $search];
                    $val = 0;

                    for ($i = 0; $i <= 2; $i++) { 
                        $char = chr($i + 97);
                        $val += $match[$search . "score" . $char];
                    }

                    $scores["scores"][] = $val;
                }
            }

            $scores["min"] = min($scores["scores"]);
            $scores["max"] = max($scores["scores"]);
            $scores["avg"] = array_sum($scores["scores"]) / count($scores["scores"]);

            return $app->response->write(json_encode(["matches" => $out, "scores" => $scores]));
        }
        return $app->response->write(json_encode($v->errors()->all()));
    })->name("team.matches");

    $app->get("/:team/pre", function ($team) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $listEntries = $app->GoogleAPI->getSheet("PreScouting")->getListFeed()->getEntries();
        $v = $app->validation;

        $v->validate([
            "team_id" => [$team, "required|int|max(5)"],
        ]);

        if ($v->passes()) {
            $pointer = false;
            $i = 0;
            $out = [];
            foreach ($listEntries as $entry) {
                $out[] = $entry->getValues();
                if ($entry->getValues()["teamnumbers"] == intval($team)) {
                    $pointer = $i;
                    break;
                }
                $i++;
            }
            /*if ($out[0]["teamnumbers"] == intval($team)) {
                return $app->response->write(json_encode("what"));
            }*/
            if ($pointer !== false) {
                return $app->response->write(json_encode($listEntries[$pointer]->getValues()));
            }
            return $app->response->write(json_encode([false, $out, $team]));
        }
        return $app->response->write(json_encode(["error" => $v->errors()->all()]));
    })->name("team.pre");

    $app->get("/:team/defences", function ($team) use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $a = $app->teams->where("team_id", "=", $team)->first()->toArray();

        $a = array_intersect_key($a, array_flip(Team::$defences));

        foreach ($a as $z => $s) {
            $s = json_decode($s, true);
            if (is_array($s)) {
                $a[$z] = array_sum($s);
            }
            $a[$z] = (int) $a[$z];
        }

        return $app->response->write(json_encode($a));
    })->name("team.defences");
});
