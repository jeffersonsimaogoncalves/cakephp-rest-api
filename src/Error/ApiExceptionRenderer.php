<?php

namespace RestApi\Error;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Error\ExceptionRenderer;
use RestApi\Controller\ApiErrorController;

/**
 * API Exception Renderer.
 *
 * Captures and handles all unhandled exceptions. Displays valid json response.
 *
 * @package RestApi\Error
 */
class ApiExceptionRenderer extends ExceptionRenderer
{
    /**
     * Handles MissingTokenException.
     *
     * @param $exception
     *
     * @return \Cake\Http\Response
     */
    public function missingToken($exception)
    {
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

    /**
     * Prepare response.
     *
     * @param  \Exception  $exception  Exception
     * @param  array  $options  Array of options
     *
     * @return \Cake\Http\Response
     */
    private function __prepareResponse($exception, $options = [])
    {
        $response = $this->_getController()->getResponse();
        $code = $this->_code($exception);
        $response->withStatus($this->_code($exception));

        Configure::write('apiExceptionMessage', $exception->getMessage());

        $responseFormat = $this->_getController()->responseFormat;
        $body = [
            $responseFormat['statusKey'] => !empty($options['responseStatus']) ? $options['responseStatus'] : $responseFormat['statusNokText'],
            $responseFormat['resultKey'] => [
                $responseFormat['errorKey'] => ($code < 500) ? 'Not Found' : 'An Internal Error Has Occurred.',
            ],
        ];

        if ((isset($options['customMessage']) && $options['customMessage']) || Configure::read('ApiRequest.debug')) {
            $body[$responseFormat['resultKey']][$responseFormat['errorKey']] = $exception->getMessage();
        }

        $response->withType('json');
        $response->withStringBody(json_encode($body));

        return $response;
    }

    /**
     * @return \RestApi\Controller\ApiErrorController
     */
    protected function _getController(): Controller
    {
        return new ApiErrorController();
    }

    /**Handles InvalidTokenFormatException.
     *
     * @param  \RestApi\Routing\Exception\InvalidTokenFormatException  $exception  InvalidTokenFormatException
     *
     * @return \Cake\Http\Response
     */
    public function invalidTokenFormat($exception)
    {
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

    /**
     * Handles InvalidTokenException.
     *
     * @param  \RestApi\Routing\Exception\InvalidTokenException  $exception  InvalidTokenException
     *
     * @return \Cake\Http\Response
     */
    public function invalidToken($exception)
    {
        return $this->__prepareResponse($exception, ['customMessage' => true]);
    }

}
