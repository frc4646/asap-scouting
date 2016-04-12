<?php

namespace App\Hash;

class Hash
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function password($password)
    {
        return password_hash(
            $password,
            PASSWORD_DEFAULT,
            ["cost" => $this->config->get("services.authentication.hash_cost")]
        );
    }

    public function passwordCheck($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function hash($input)
    {
        return hash("sha512", $input);
    }

    public function hashCheck($known, $user)
    {
        return (bool) hash_equals($known, $user);
    }
}
