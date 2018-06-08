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
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
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

    /**
     * @param \Cake\Core\PluginApplicationInterface $app
     */
    public function bootstrap(PluginApplicationInterface $app)
    {
        parent::bootstrap($app);
        try {
            Configure::load('RestApi.api', 'default', false);
            Configure::load('api', 'default', true);
        } catch (\Exception $e) {
            // nothing
        }
    }
}