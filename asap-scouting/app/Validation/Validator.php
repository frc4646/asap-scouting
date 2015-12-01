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
            'email' => [
                'uniqueEmail' => 'That email is already in use.'
            ],

            'username' => [
                'uniqueUsername' => 'That username is already in use.'
            ],
            'not_null' => [
                'not_null' => 'That variable is null.'
            ]
        ]);

        $this->addRuleMessages([
            'matchesCurrentPassword'=> 'That does not match your current password.',
            'alnumDashSpc' => '{field} must be alphanumeric with dashes underscores, and spaces permitted.'
        ]);
    }

    public function validate_uniqueEmail($value, $input, $args)
    {
        $user = $this->user->where('email', $value);

        if ($this->auth && $this->auth->email === $value) {
            return true;
        }

        return ! (bool) $user->count();
    }

    public function validate_uniqueUsername($value, $input, $args)
    {
        return ! (bool) $this->user->where('username', $value)->count();
    }

    public function validate_matchesCurrentPassword($value, $input, $args)
    {
        if ($this->auth && $this->hash->passwordCheck($value, $this->auth->password)) {
            return true;
        }

        return false;
    }

    public function validate_not_null($value, $input, $args)
    {
        return ! (bool) is_null($value);
    }

    public function validate_alnumDashSpc($value, $input, $args)
    {
        return (bool) preg_match('/^[\s\pL\pM\pN_-]+$/u', $value);
    }

    public function constructArray($success = true, $validationErrors = null, $otherErrors = null, $url, $json = true)
    {
        if (!is_null($validationErrors)) {
            $errorArray = [];
            $v = 0;

            foreach ($validationErrors->keys() as $var) {
                $errorArray[] = [
                    "item" => $var,
                    "message" =>$validationErrors->first($var),
                ];
            }
        }

        $array = [
                "success" => $success,
                "errors" => (isset($errorArray)) ? $errorArray : "",
                "url" => $url,
        ];

        if (is_null($validationErrors) && ! is_null($otherErrors)) {
            $array["errors"] = $otherErrors;
        }

        if ($json) {
            return json_encode($array);
        }

        return $array;
    }
}
