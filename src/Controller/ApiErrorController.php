<?php

namespace RestApi\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventInterface;

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
     * @param \Cake\Event\EventInterface $event Event.
     *
     * @return null
     * @throws \Exception
     */
    public function beforeRender(EventInterface $event)
    {
        $this->httpStatusCode = $this->getResponse()->getStatusCode();

        if (Configure::read('ApiRequest.debug') && isset($this->viewVars['error'])) {
            $error = $this->viewVars['error'];
            /** @var \Exception $error */
            $this->apiResponse[$this->responseFormat['messageKey']] = $error->getMessage();
        } else {
            $this->apiResponse[$this->responseFormat['messageKey']] = !empty($messageArr[$this->httpStatusCode]) ? $messageArr[$this->httpStatusCode] : 'Unknown error!';
        }

        parent::beforeRender($event);

        $this->viewBuilder()->setClassName('RestApi.ApiError');

        return null;
    }
}
