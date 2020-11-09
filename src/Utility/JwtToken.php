<?php

namespace RestApi\Utility;

use Cake\Core\Configure;
use Firebase\JWT\JWT;

/**
 * Class JwtToken
 *
 * JWT token utility class.
 *
 * @package RestApi\Utility
 */
class JwtToken
{

    /**
     * Generates a token based on payload
     *
     * @param  mixed  $payload  Payload data to generate token
     *
     * @return string|bool Token or false
     */
    public static function generateToken($payload = null)
    {
        if (empty($payload)) {
            return false;
        }

        return JWT::encode($payload, Configure::read('ApiRequest.jwtAuth.cypherKey'),
            Configure::read('ApiRequest.jwtAuth.tokenAlgorithm'));
    }
}
