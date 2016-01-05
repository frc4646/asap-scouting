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

namespace app\Helpers;

class Hash
{
    protected $config;

    /**
     * Class Constructor
     *
     * @param object $config Config Object
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Password Hash
     *
     * @param  string $password Password to hash
     *
     * @return string Password Hashed with the strongest implemented algorithm
     */
    public function password($password)
    {
        return password_hash(
            $password,
            $this->config->get("app.hash.algo"),
            ["cost" => $this->config->get("app.hash.cost")]
        );
    }

    /**
     * Timing Safe way to verify a password hash
     *
     * @param  string  $password Hashed password to verify
     * @param  string  $hash     Hash to check against
     *
     * @return boolean Whether the two hash's match
     */
    public function passwordCheck($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Plain Hashinh (SHA256)
     *
     * @param  string $input String to hash
     *
     * @return string The hashed version
     */
    public function hash($input)
    {
        return hash("sha256", $input);
    }

    /**
     * Timing safe way to verify a hash
     *
     * @param  string  $known Hash to verify against
     * @param  string  $user  Hash to verify
     *
     * @return boolean Whether the hash's match
     */
    public function hashCheck($known, $user)
    {
        return hash_equals($known, $user);
    }
}
