<?php
/**
 * Created by PhpStorm.
 * User: Jefferson Simão Gonçalves
 * Email: gerson.simao.92@gmail.com
 * Date: 08/06/2018
 * Time: 14:51
 */

namespace RestApi;

use Cake\Core\BasePlugin;
use RestApi\Middleware\RestApiMiddleware;

/**
 * Class Plugin
 *
 * @author Jefferson Simão Gonçalves <gerson.simao.92@gmail.com>
 *
 * @package RestApi
 */
class Plugin extends BasePlugin
{
    protected $routesEnabled = false;

    /**
     * @param \Cake\Http\MiddlewareQueue $middleware
     *
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middleware)
    {
        $middleware->add(new RestApiMiddleware());

        return $middleware;
    }
}