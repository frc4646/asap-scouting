<?php

/**
 * FRC Scouting Application (Written for FRC Team 4646 ASAP )
 *
 * @author      Alexander Young <meun5@team4646.org>
 * @copyright   2015 Alexander Young
 * @link        https://github.com/meun5/asap-scouting
 * @license     https://github.com/meun5/asap-scouting/blob/master/LICENSE
 * @version     0.3.0
 */

namespace app\Validation;

use Violin\Violin;

use app\User\User;
use app\Helpers\Hash;

class Validator extends Violin
{
    protected $user;

    protected $hash;

    protected $auth;

    public function __construct(User $user, Hash $hash, $auth = null)
    {
        $this->user = $user;

        $this->hash = $hash;

        $this->auth = $auth;


        $this->addFieldMessages([
            "email" => [
                "uniqueEmail" => "That email is already in use."
            ],
            "username" => [
                "uniqueUsername" => "That username is already in use."
            ],
        ]);

        /*$this->addRuleMessages([
            "alnumDashSpc" => "{field} must be alphanumeric with dashes underscores, and spaces permitted."
        ]);*/
    }

    public function validateUniqueEmail($value, $input, $args)
    {
        $user = $this->user->where("email", $value);

        if ($this->auth && $this->auth->email === $value) {
            return true;
        }

        return ! (bool) $user->count();
    }

    public function validateUniqueUsername($value, $input, $args)
    {
        return ! (bool) $this->user->where("username", $value)->count();
    }

    /*public function validate_alnumDashSpc($value, $input, $args)
    {
        return (bool) preg_match("/^[\s\pL\pM\pN_-]+$/u", $value);
    }*/
}
