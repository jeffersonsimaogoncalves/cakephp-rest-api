<?php

namespace RestApi\Routing\Exception;

use Cake\Core\Exception\Exception;

/**
 * Class MissingTokenException
 *
 * Missing token exception - used when an authorization token
 * is missing
 *
 * @package RestApi\Routing\Exception
 */
class MissingTokenException extends Exception
{

    /**
     * MissingTokenException constructor.
     *
     * @param string|array $message Either the string of the error message, or an array of attributes
     *   that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param int $code The code of the error, is also the HTTP status code for the error.
     * @param \Exception|null $previous the previous exception.
     */
    public function __construct($message = null, $code = 401, $previous = null)
    {
        if (empty($message)) {
            $message = 'Token is missing. Please pass the token in request in the form of header, query parameter or post data field.';
        }
        parent::__construct($message, $code, $previous);
    }
}
