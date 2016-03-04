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

use app\Teams\TeamImage;
use app\Teams\Team;

$app->group("/submit", function () use ($app) {
    //$app->response->headers->set("Content-Type", "application/json");
    /*header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
    $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
    $app->response->headers->set("Pragma", "no-cache");
    $app->expires("-1 week");
    $app->lastModified(strtotime("-1 week"));*/
    $app->post("/photo", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));
        $id = $app->request->post()["team_id"];
        $v = $app->validation;
        
        $v->validate([
            "team_id" => [$id, "required|int|max(5)"]
        ]);
        
        if ($v->passes()) {
            $post_dir = INC_ROOT . "/public/img/teams/{$id}";
            $name = $app->hash->hash($app->randomlib->generateString(32));
            
            $yn = TeamImage::set([
                "photo" => $_FILES["photo"],
                "move_dir" => $post_dir,
                "name" => $name,
            ]);
            
            if ($yn) {
                $file = $name . "." . explode(".", $_FILES["photo"]["name"])[1];
                $este = $app->teams->where("team_id", $id)->first();
                
                $details = json_decode($este->details, true);
                
                $details["images"][] = "/img/teams/{$id}/{$file}";
                
                $este->update([
                    "details" => json_encode($details),
                ]);
                return $app->response->write(json_encode(["success" => true, "images" => $details["images"]]));
            }

            return $app->response->write(json_encode(["success" => false, "error" => "Failed to set Team Image."]));
        }
        
        return $app->response->write(json_encode(["success" => false, "error" => (array) $v->errors()->all()]));
    })->name("submit.photo");
    
    $app->post("/hprank", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));

        $post = $app->request->post();
        $v = $app->validation;

        $v->validate([
            "hprank" => [$post["hprank"], "required|alpha"],
            "team_id" => [$post["team_id"], "required|int"],
        ]);

        if ($v->passes()) {
            $curr = $app->teams->where("team_id", $post["team_id"])->first();
            $details = json_decode($curr->details, true);
            
            $details["hprank"] = $post["hprank"];
            
            $curr->update([
                "details" => json_encode($details),
            ]);

            return $app->response->write(json_encode($details));
        }
        return $app->response->write(json_encode($v->errors()->all()));
    })->name("submit.hprank");

    $app->post("/comment", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));

        $post = $app->request->post();
        $v = $app->validation;
        
        $v->validate([
            "comment" => [$post["comment"], "required"],
            "team_id" => [$post["team_id"], "required|int"],
        ]);
        
        if ($v->passes()) {
            $curr = $app->teams->where("team_id", $post["team_id"])->first();
            $details = (array) json_decode($curr->details);
            
            $details["comments"][] = htmlentities($post["comment"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
            
            $curr->update([
                "details" => json_encode($details),
            ]);
            
            echo json_encode($details);
            return;
        }
        
        echo json_encode($v->errors());
        return;
    })->name("submit.comment");

    $app->post("/pit", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));

        $post = $app->request->post();
        $v = $app->validation;

        if (!array_key_exists("team-comment", $post)) {
            $post["team-comment"] = [];
        }

        $v->validate([
            "team-name" => [$post["team-name"], "required"],
            "team-num" => [$post["team-num"], "required|int|max(5)"],
            "bot_type" => [$post["bot_type"], "required|alnumDash"],
            "team-comment" => [$post["team-comment"], "array"],
            "nonce" => [$post["nonce"], "required"],
        ]);

        if ($v->passes()) {
            //$app->response->write(json_encode([base64_encode($app->randomlib->generateString(32)), $post["team-name"]]));
            $f = 0;
            foreach ($post["team-comment"] as $comment) {
                $post["team-comment"][$f] = htmlentities($comment, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
                $f++;
            }
            if (!$app->teams->where("nonce", $post["nonce"])->exists()) {
                $details = Team::$defaults;

                $details["name"] = htmlentities($post["team-name"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
                $details["bot_type"] = htmlentities($post["bot_type"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
                if (!empty($post["team-comment"])) {
                    $details["comments"] = $post["team-comment"];
                }

                $newTeam = $app->teams->create([
                    "team_id" => $post["team-num"],
                    "details" => json_encode($details),
                    "nonce" => base64_encode($app->randomlib->generateString(32)),
                ]);

                return $app->response->write(json_encode(["item" => $newTeam]));
            } else {
                $id = $app->teams->where("team_id", $post["team-num"])->first();
                $details = json_decode($id->details, true);

                $details["name"] = htmlentities($post["team-name"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
                $details["bot_type"] = htmlentities($post["bot_type"], ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, "UTF-8");
                $details["comments"] = $post["team-comment"];

                $newTeam = $id->update([
                    "team_id" => $post["team-num"],
                    "details" => json_encode($details),
                ]);

                return $app->response->write(json_encode(["success" => $newTeam, "orgin" => $app->teams->where("team_id", $post["team-num"])->first()]));
            }
            return $app->response->write(json_encode(["error" => "That team already exists!"]));
        }
        return $app->response->write(json_encode(["error" => $v->errors/*()/*->all()*/]));
    })->name("submit.pit");

    $app->post("/pre", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        header("Cache-Control: no-cache, no-store, must-revalidate post-check=0, pre-check=0");
        $app->response->headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
        $app->response->headers->set("Pragma", "no-cache");
        $app->expires("-1 week");
        $app->lastModified(strtotime("-1 week"));

        $v = $app->validation;
        $post = $app->request->post();

        $v->validate([
            "teamnames" => [$post["teamnames"] ? $post["teamnames"] : null, "required"],
            "teamnumbers" => [$post["teamnumbers"] ? $post["teamnumbers"] : null, "required|int|max(6)"],
            "website" => [$post["website"] ? $post["website"] : null, "url"],
            "email" => [$post["email"] ? $post["email"] : null, "email"],
            "city" => [$post["city"] ? $post["city"] : null, "alnumDash"],
            "state" => [$post["state"] ? $post["state"] : null, "alnumDash"],
        ]);

        if ($v->passes()) {
            if (!$app->GoogleAPI->getSheet("PreScouting")->getListFeed(["teamnumbers" => $post["teamnumbers"]])) {
                $app->GoogleAPI->getSheet("PreScouting")->getListFeed()->insert([
                    "teamnames" => $post["teamnames"],
                    "teamnumbers" => $post["teamnumbers"],
                    "website" => $post["website"] ? $post["website"] : "",
                    "email" => $post["email"] ? $post["email"] : "",
                    "city" => $post["city"] ? $post["city"] : "",
                    "state" => $post["state"] ? $post["state"] : "",
                ]);
                return $app->response->write(json_encode($post));
            }
            $entries = $app->GoogleAPI->getSheet("PreScouting")->getListFeed(["teamnumbers" => $post["teamnumbers"]])->getEntries()[0];
            $values = $entries->getValues();
            foreach ($post as $item => $value) {
                $values[$item] = $value;
            }
            $entries->update($values);

            return $app->response->write(json_encode($values));
        }
        return $app->response->write(json_encode(["error" => $v->errors/*()/*->all()*/]));
        //return $app->response->write(json_encode($app->request->post()));
    })->name("submit.pre");
});
