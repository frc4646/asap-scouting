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
