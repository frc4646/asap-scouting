<?php

namespace app\Teams;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Team extends Eloquent
{
    protected $table = "teams";

    protected $fillable = [
        "team_id",
        "details"
    ];

    public static $defaults = [
        "defences"  => [
            "portcullis" => false,
            "cheval_de_frise" => false,
            "moat" => false,
            "ramparts" => false,
            "drawbridge" => false,
            "sally_port" => false,
            "rock_wall" => false,
            "rough_terrain" => false,
            "low_bar" => false,
        ],
        "images" => [],
        "comments" => [],
        "bot_type" => "offense",
        "runOnce" => true,
    ];
}
