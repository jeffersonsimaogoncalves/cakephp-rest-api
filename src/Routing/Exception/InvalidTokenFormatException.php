<?php

namespace RestApi\Routing\Exception;

use Cake\Core\Exception\Exception;

/**
 * Class InvalidTokenFormatException
 *
 * Invalid token format exception - used when an authorization header format
 * is wrong.
 *
 * @package RestApi\Routing\Exception
 */
class InvalidTokenFormatException extends Exception
{

    /**
     * InvalidTokenFormatException constructor.
     *
     * @param  string|array  $message  Either the string of the error message, or an array of attributes
     *                                  that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param  int  $code  The code of the error, is also the HTTP status code for the error.
     * @param  \Exception|null  $previous  the previous exception.
     */
    public function __construct($message = null, $code = 401, $previous = null)
    {
        if (empty($message)) {
            $message = 'Format is Authorization: Bearer [token].';
        }
        parent::__construct($message, $code, $previous);
    }
}
