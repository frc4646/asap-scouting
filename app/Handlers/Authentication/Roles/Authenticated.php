<?php

namespace App\Authentication\Roles;

use App\Authentication\Filter;
use Slim\App;

class Authenticated extends Filter
{
    protected $app;
    
    /**
     * Sets local app variable
     *
     * @param      Slim\App  $app    App Instance
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        
        $this->auth = $app->getContainer()->get("auth");
    }
    
    /**
     * Determines if current user is authenticated
     *
     * @param      Auth\User   $user   Current User
     *
     * @return     boolean
     */
    protected function isAuthorized($user)
    {
        return ! (bool) empty($this->auth->getRaw());
    }
}
