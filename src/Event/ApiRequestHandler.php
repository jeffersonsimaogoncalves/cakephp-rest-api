<?php

namespace RestApi\Event;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use RestApi\Utility\ApiRequestLogger;

/**
 * Event listner for API requests.
 *
 * This class binds the different events and performs required operations.
 *
 * @package RestApi\Event
 */
class ApiRequestHandler implements EventListenerInterface
{

    /**
     * Event bindings.
     *
     * @return array
     */
    public function implementedEvents(): array
    {
        return [
            'Dispatcher.beforeDispatch' => [
                'callable' => 'beforeDispatch',
                'priority' => 0,
            ],
            'Dispatcher.afterDispatch' => [
                'callable' => 'afterDispatch',
                'priority' => 9999,
            ],
            'Controller.shutdown' => [
                'callable' => 'shutdown',
                'priority' => 9999,
            ],
        ];
    }

    /**
     * Handles incoming request and its data.
     *
     * @param  Event  $event  The beforeDispatch event
     *
     * @return array|mixed|null
     */
    public function beforeDispatch(Event $event)
    {
        $this->buildResponse($event);
        Configure::write('requestLogged', false);
        $request = $event->getData('request');
        if ('OPTIONS' === $request->method()) {
            $event->stopPropagation();
            $response = $event->getData('response');
            $response->withStatus(200);

            return $response;
        }

        if (empty($request->getData)) {
            $request->getData = $request->input('json_decode', true);
        }
    }

    /**
     * Prepares the response object with content type and cors headers.
     *
     * @param  Event  $event  The event object either beforeDispatch or afterDispatch
     *
     * @return bool true
     */
    private function buildResponse(Event $event)
    {
        $request = $event->getData('request');
        $response = $event->getData('response');
        $response->type('json');
        $response->cors($request)
            ->allowOrigin(Configure::read('ApiRequest.cors.origin'))
            ->allowMethods(Configure::read('ApiRequest.cors.allowedMethods'))
            ->allowHeaders(Configure::read('ApiRequest.cors.allowedHeaders'))
            ->allowCredentials()
            ->maxAge(Configure::read('ApiRequest.cors.maxAge'))
            ->build();

        return true;
    }

    /**
     * Updates response headers.
     *
     * @param  Event  $event  The afterDispatch event
     */
    public function afterDispatch(Event $event)
    {
        $this->buildResponse($event);
    }

    /**
     * Logs the request and response data into database.
     *
     * @param  Event  $event  The shutdown event
     */
    public function shutdown(Event $event)
    {
        $request = $event->getSubject()->request;
        /** @var \Cake\Http\ServerRequest $request */
        if ('OPTIONS' === $request->getMethod()) {
            return;
        }

        if (!Configure::read('requestLogged') && Configure::read('ApiRequest.log')) {
            ApiRequestLogger::log($request, $event->getSubject()->response);
        }
    }

}
