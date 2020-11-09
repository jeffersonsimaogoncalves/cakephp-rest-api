<?php

namespace RestApi\Routing\Exception;

use Cake\Core\Exception\Exception;

/**
 * Class InvalidTokenException
 *
 * Invalid token exception - used when an authorization token
 * is invalid or blank
 *
 * @package RestApi\Routing\Exception
 */
class InvalidTokenException extends Exception
{

    /**
     * InvalidTokenException constructor.
     *
     * @param  string|array  $message  Either the string of the error message, or an array of attributes
     *                                  that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param  int  $code  The code of the error, is also the HTTP status code for the error.
     * @param  \Exception|null  $previous  the previous exception.
     */
    public function __construct($message = null, $code = 401, $previous = null)
    {
        if (empty($message)) {
            $message = 'Invalid or empty token.';
        }
        parent::__construct($message, $code, $previous);
    }
}
