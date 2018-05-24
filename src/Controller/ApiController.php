<?php

namespace RestApi\Controller;

use Cake\Event\Event;

/**
 * Api Controller
 *
 * Provides basic functionality for building REST APIs
 *
 * @package RestApi\Controller
 */
class ApiController extends AppController
{

    /**Before render callback.
     *
     * @param Event $event The beforeRender event.
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function beforeRender(Event $event)
    {
        $this->viewBuilder()->setClassName('RestApi.Api');

        return parent::beforeRender($event);
    }
}
