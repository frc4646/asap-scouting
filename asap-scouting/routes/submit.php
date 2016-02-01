<?php

use \app\Teams\TeamImage;

$app->group("/submit", function () use ($app) {
    $app->post("/photo", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
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
        
        return $app->response->write(json_encode(["success" => false, "error" => (array) $v->errors()]));
    })->name("submit.photo");
    
    $app->post("/comment", function () use ($app) {
        $app->response->headers->set("Content-Type", "application/json");
        $post = $app->request->post();
        $v = $app->validation;
        
        $v->validate([
            "comment" => [$post["comment"], "required"],
            "team_id" => [$post["team_id"], "required|int"],
        ]);
        
        if ($v->passes()) {
            $curr = $app->teams->where("team_id", $post["team_id"])->first();
            $details = (array) json_decode($curr->details);
            
            $details["comments"][] = $post["comment"];
            
            $curr->update([
                "details" => json_encode($details),
            ]);
            
            echo json_encode($details);
            return;
        }
        
        echo json_encode($v->errors());
        return;
    })->name("submit.comment");
});
