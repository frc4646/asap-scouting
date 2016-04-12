<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

class CSRF
{
    protected $csrf;
    
    protected $view;

    public function __construct(Guard $csrf, Twig $view)
    {
        $this->csrf = $csrf;
        
        $this->view = $view;
    }

    /**
     * CSRF Middleware View Attachment
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $request = $this->csrf->generateNewToken($request);
        
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);
        
        $tokenArray = [
            $nameKey => $name,
            $valueKey => $value,
            "keys" => [
                $nameKey => $nameKey,
                $valueKey => $valueKey,
            ],
        ];

        // Update response with added token header
        $response = $response->withAddedHeader("X-CSRF-Token", json_encode($tokenArray));
        
        $this->view["csrf"] = $tokenArray;
        
        return $next($request, $response);
    }
}
