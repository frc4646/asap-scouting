<?php

namespace App\Authentication;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

class Filter
{
    protected $auth;

    protected $container;
    
    protected $app;

    protected $log;
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = $app->getContainer();
        $this->auth = $this->container->get("auth");
        $this->log = $this->container->get("log");
    }
    
    /**
     * Authorization middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $user = $this->auth->getAuth();
        if (!$this->isAuthorized($user)) {
            $this->log->critical("Unauthenticated " . $request->getMethod() . " request from " . $request->getServerParams()["REMOTE_ADDR"] . " path:" . $request->getUri()->getPath());
            return $response->withStatus(403)->withHeader("Content-type", "text/html")->write("<h1>Unauthenticated Access Not Allowed</h1>");
        }
        
        return $next($request, $response);
    }

    /**
     * Check if the given user is authorized.
     *
     * @param  string $user The user to check.
     *
     * @return boolean true if the user is authorized, false otherwise.
     */
    protected function isAuthorized($user)
    {
        return false;
    }
}
