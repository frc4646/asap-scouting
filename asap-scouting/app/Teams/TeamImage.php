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

class TeamImage
{
    public static function set(array $data)
    {
        if (self::checkKeys($data)) {
            self::mkpath($data["move_dir"]);
            if ($data["photo"]["error"] == false) {
                move_uploaded_file($data["photo"]["tmp_name"], 
                                   $data["move_dir"] . "/" . $data["name"] . "." . explode(".", $data["photo"]["name"])[1]
                                  );
                return true;
            }
        }
        
        return false;
    }
    
    protected static function mkpath($path)
    {
        if (is_string($path) && !is_dir($path)) {
            if (mkdir($path)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected static function checkKeys(array $item)
    {
        if (array_key_exists("photo", $item) && array_key_exists("move_dir", $item) && array_key_exists("name", $item)) {
            return true;
        }
        
        return false;
    }
}
