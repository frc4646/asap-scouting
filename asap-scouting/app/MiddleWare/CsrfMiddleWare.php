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

namespace app\MiddleWare;

use Slim\Middleware;
use Exception;

class CsrfMiddleWare extends Middleware
{
    protected $key;

    public function call()
    {
        $this->key = $this->app->config->get('csrf.key');

        $this->app->hook('slim.before', [$this, 'check']);

        $this->next->call();
    }

    public function check()
    {
        if (!isset($_SESSION[$this->key])) {
            $_SESSION[$this->key] = $this->app->hash->hash(
                $this->app->randomlib->generateString(128)
            );
        }

        $token = $_SESSION[$this->key];

        if (in_array($this->app->request()->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $submittedToken = $this->app->request()->post($this->key) ?: '';

            if (!$this->app->hash->hashCheck($token, $submittedToken)) {
                throw new Exception("CSRF Token Mismatch");
            }
        }

        $this->app->view()->appendData([
            'csrf_key' => $this->key,
            'csrf_token' => $token
        ]);
    }
}
