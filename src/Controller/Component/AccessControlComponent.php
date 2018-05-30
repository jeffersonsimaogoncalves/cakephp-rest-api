<?php

namespace RestApi\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Firebase\JWT\JWT;
use RestApi\Routing\Exception\InvalidTokenException;
use RestApi\Routing\Exception\InvalidTokenFormatException;
use RestApi\Routing\Exception\MissingTokenException;

/**
 * Access control component class.
 *
 * Handles user authentication and permission.
 *
 * @package RestApi\Controller\Component
 */
class AccessControlComponent extends Component
{

    /**
     * Startup method.
     *
     * Handles request authentication using JWT.
     *
     * @param Event $event The startup event
     *
     * @return \Cake\Http\Response|boolean
     */
    public function startup(Event $event)
    {
        if (Configure::read('ApiRequest.jwtAuth.enabled')) {
            return $this->_performTokenValidation($event);
        }

        return true;
    }

    /**
     * Performs token validation.
     *
     * @param Event $event The startup event
     *
     * @return bool
     */
    protected function _performTokenValidation(Event $event)
    {
        $request = $event->getSubject()->request;

        /** @var \Cake\Http\ServerRequest $request */
        if (!empty($request->getParam('allowWithoutToken')) && $request->getParam('allowWithoutToken')) {
            return true;
        }

        $header = $request->getHeaderLine('Authorization');

        if (!empty($header)) {
            $parts = explode(' ', $header);

            if (count($parts) < 2 || empty($parts[0]) || !preg_match('/^Bearer$/i', $parts[0])) {
                throw new InvalidTokenFormatException();
            }

            $token = $parts[1];
        } elseif (!empty($this->getController()->getRequest()->getQuery('token'))) {
            $token = $this->getController()->getRequest()->getQuery('token');
        } elseif (!empty($request->getData('token'))) {
            $token = $request->getData('token');
        } else {
            throw new MissingTokenException();
        }

        try {
            $payload = JWT::decode($token, Configure::read('ApiRequest.jwtAuth.cypherKey'), [Configure::read('ApiRequest.jwtAuth.tokenAlgorithm')]);
        } catch (\Exception $e) {
            throw new InvalidTokenException();
        }

        if (empty($payload)) {
            throw new InvalidTokenException();
        }

        $controller = $this->_registry->getController();

        /** @var \RestApi\Controller\AppController $controller */
        $controller->jwtPayload = $payload;

        $controller->jwtToken = $token;

        Configure::write('accessToken', $token);

        return true;
    }
}
