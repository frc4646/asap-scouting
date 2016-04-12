<?php

namespace App\Authentication\Roles;

use App\Authentication\Filter;
use Slim\App;

class Admin extends Filter
{
    /**
     * Calls Parent Constructor
     *
     * @param      Slim\App  $app    App Instance
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }
    
    protected function isAuthorized($user)
    {
        if (!is_null($user)) {
            return (bool) $user->hasPermission("is_admin");
        }

        return false;
    }
}
