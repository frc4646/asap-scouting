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

namespace app\MiddleWare;

use Slim\Middleware;
use Exception;

class BeforeMiddleWare extends Middleware
{
    /**
     * The middleware "call" hook
     */
    public function call()
    {
        $this->app->hook("slim.before", [$this, "run"]);

        $this->next->call();
    }

    /**
     * Function to run when middleware is called
     */
    public function run()
    {
        if (isset($_SESSION[$this->app->config->get("auth.session")])) {
            $this->app->auth = $this->app->user
                                         ->where("id", $_SESSION[$this->app->config->get("auth.session")])
                                         ->first();
        }

        if ($this->app->config->get("app.hash.cost") < 10) {
            throw new Exception("Hash Cost is too low");
        }

        $this->checkRememberMe();

        $this->app->view()->appendData([
            "auth" => $this->app->auth,
            "url"  => [
                "base"      => $this->app->config->get("app.url"),
                "current"   => $this->app->request->getPathInfo(),
            ],
            "isMobile" => (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile") !== false) ? true : false,
        ]);
    }

    /**
     * Checks if a user has hit "remember me"
     * @return object Redirect if "remember me" is invalid
     */
    protected function checkRememberMe()
    {
        if ($this->app->getCookie($this->app->config->get("auth.remember")) && !$this->app->auth) {
            $data = $this->app->getCookie($this->app->config->get("auth.remember"));
            $credentials = explode("___", $data);

            if (empty(trim($data)) || count($credentials) !== 2) {
                return $this->app->response->redirect($this->app->urlFor("home"));
            } else {
                $identifier = $credentials[0];
                $token = $this->app->hash->hash($credentials[1]);

                $user = $this->app->user
                    ->where("remember_identifier", $identifier)
                    ->first();

                if ($user) {
                    if ($this->app->hash->hashCheck($token, $user->remember_token)) {
                        $_SESSION[$this->app->config->get("auth.session")] = $user->id;
                        $this->app->auth = $this->app->user->where("id", $user->id)->first();
                    } else {
                        $user->removeRememberCredentials();
                    }
                }
            }
        }
    }
}
