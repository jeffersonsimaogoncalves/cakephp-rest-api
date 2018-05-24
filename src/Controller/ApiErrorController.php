<?php

namespace RestApi\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Api error controller
 *
 * This controller will sets configuration to render errors
 *
 * @package RestApi\Controller
 */
class ApiErrorController extends AppController
{

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\Event $event Event.
     * @return null
     * @throws \Exception
     */
    public function beforeRender(Event $event)
    {
        $this->httpStatusCode = $this->getResponse()->getStatusCode();

        if (Configure::read('ApiRequest.debug') && isset($this->viewVars['error'])) {
            $this->apiResponse[$this->responseFormat['messageKey']] = $this->viewVars['error']->getMessage();
        } else {
            $this->apiResponse[$this->responseFormat['messageKey']] = !empty($messageArr[$this->httpStatusCode]) ? $messageArr[$this->httpStatusCode] : 'Unknown error!';
        }

        parent::beforeRender($event);

        $this->viewBuilder()->setClassName('RestApi.ApiError');

        return null;
    }
}
