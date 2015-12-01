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

namespace app\User;

use Illuminate\Database\Eloquent\Model as Eloquent;

class UserPermission extends Eloquent
{
    protected $table = 'permissions';

    protected $fillable = [
        'is_admin',
    ];

    public static $defaults = [
        'is_admin' => false,
    ];
}
