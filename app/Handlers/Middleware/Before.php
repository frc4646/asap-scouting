<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;
use Slim\Router;
use Slim\App;
use Psr\Log\LoggerInterface;
use Carbon\Carbon;

class Before
{
    protected $view;

    protected $log;
    
    public function __construct(Twig $view, LoggerInterface $log)
    {
        $this->view = $view;

        $this->log = $log;
    }
    
    /**
     * Before Middleware View Attachment
     *
     * @param  Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                $next     Next middleware
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $this->view["url"] = [
            "current" => $request->getUri()->getPath(),
            "remote" => $request->getServerParams()["REMOTE_ADDR"],
        ];

        $this->view["app"] = [
            "version" => [
                "app" => APP_VERSION,
                "slim" => App::VERSION,
            ],
        ];

        $this->view["time"] = Carbon::now();

        $this->log->info("Incoming " . $request->getMethod() . " request from " . $request->getServerParams()["REMOTE_ADDR"] . " path:" . $request->getUri()->getPath());

        return $next($request, $response);
    }
}