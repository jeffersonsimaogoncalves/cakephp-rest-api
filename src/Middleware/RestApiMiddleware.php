<?php

namespace RestApi\Middleware;

use Cake\Core\App;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Event\EventManager;
use RestApi\Event\ApiRequestHandler;
use Throwable;

/**
 * Class RestApiMiddleware
 *
 * @package RestApi\Middleware
 */
class RestApiMiddleware extends ErrorHandlerMiddleware
{
    /**
     * @var string
     */
    public $exceptionRenderer;

    /**
     * Override ErrorHandlerMiddleware and add custom exception renderer
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  The request.
     * @param  \Psr\Http\Message\ResponseInterface  $response  The response.
     * @param  callable  $next  Callback to invoke the next middleware.
     *
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function __invoke($request, $response, $next)
    {
        try {
            $params = (array) $request->getAttribute('params', []);
            if (isset($params['controller'])) {
                $controllerName = $params['controller'];
                $firstChar = substr($controllerName, 0, 1);
                if (strpos($controllerName, '\\') !== false ||
                    strpos($controllerName, '/') !== false ||
                    strpos($controllerName, '.') !== false ||
                    $firstChar === strtolower($firstChar)
                ) {
                    return $next($request, $response);
                }
                $className = App::className($controllerName, 'Controller', 'Controller');
                $controller = ($className) ? new $className() : null;
                if ($controller && 'RestApi\Controller\ApiController' === get_parent_class($controller)) {
                    $this->exceptionRenderer = 'RestApi\Error\ApiExceptionRenderer';
                    EventManager::instance()->on(new ApiRequestHandler());
                }
                unset($controller);
            }

            return $next($request, $response);
        } catch (Throwable $e) {
            return $this->handleException($e, $request, $response);
        }
    }
}
