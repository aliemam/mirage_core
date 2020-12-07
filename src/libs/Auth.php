<?php
/*
  +------------------------------------------------------------------------+
  | Mirage Framework                                                       |
  +------------------------------------------------------------------------+
  | Copyright (c) 2018-2020                                                |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file LICENSE.txt.                             |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to help@aemirage.com so we can send you a copy immediately.            |
  +------------------------------------------------------------------------+
  | Authors: Ali Emamhadi <aliemamhadi@aemirage.com>                       |
  +------------------------------------------------------------------------+
*/

/**
 * This is part of Mirage Micro Framework
 *
 * @author Ali Emamhadi <aliemamhadi@gmail.com>
 */

namespace Mirage\Libs;

use ErrorException;
use Mirage\Constants\Err;
use Mirage\Exceptions\HttpException;
use Phalcon\Security;
use Firebase\JWT\JWT;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use stdClass;

/**
 * Class Auth
 *
 * This class handles just JWT authentication method.
 *
 * @package Mirage\Libs
 */
class Auth
{
    /** @var stdClass jwt token stores in this variable */
    private static ?stdClass $jwt_payload;

    /**
     * In case of error occurred, this function handle that error based on $bypass_error variable.
     * Variable $throw_exception_on_error controls action of throwing error,
     * and terminate process if anything goes wrong.
     * So do not set it to true unless you know what you are doing. This is matter of app security.
     * @param HttpException $http
     * @param bool $throw_exception_on_error
     * @return void
     * @throws HttpException
     * @throws ErrorException
     */
    private static function error(HttpException $http, bool $throw_exception_on_error): void
    {
        if (!$throw_exception_on_error) {
            L::d($http->getMessage());
        } else {
            (new Security())->hash((string)rand());
            throw $http;
        }
    }

    /**
     * @param array $payload This is payload body of jwt token.
     * @return string|null
     * @throws HttpException
     * @throws ErrorException
     */
    public static function generateToken(array $payload): ?string
    {
        $hash_key = Config::get('app.security.jwt_hash_key');
        if (!isset($hash_key)) {
            self::error(
                new HttpException(
                    Err::AUTH_JWT_HASH_KEY_NOT_EXIST,
                    'hash key is not set yet. please set hash key first.'
                ),
                true
            );
            return null;
        }
        L::d('generate jwt with payload: ' . json_encode($payload));
        return JWT::encode($payload, $hash_key, 'HS256');
    }

    /**
     * @param ?string $jwt_token This is given jwt token to check
     * @param bool $throw_exception_on_error if this variable was set to false,
     * on any authentication failure, this class does not throw an error so be careful with it.
     * @return bool
     * @throws HttpException
     * @throws ErrorException
     */
    public static function checkToken(?string $jwt_token, bool $throw_exception_on_error = true): bool
    {
        L::d("checking token: $jwt_token");
        if (!$throw_exception_on_error) {
            L::w('!!!CHECK AUTH TOKEN WARNING ---> BYPASSED ON ERROR!!!');
        }

        if (!isset($jwt_token) || $jwt_token == '') {
            self::error(
                new HttpException(Err::AUTH_HEADER_NOT_FOUND, 'AUTHORIZATION Header not found'),
                $throw_exception_on_error
            );
            return false;
        }
        
        $hash_key = Config::get('app.security.jwt_hash_key');
        if (!isset($hash_key)) {
            self::error(
                new HttpException(
                    Err::AUTH_JWT_HASH_KEY_NOT_EXIST,
                    'hash key is not set yet. please set hash key first.'
                ),
                $throw_exception_on_error
            );
            return false;
        }

        try {
            self::$jwt_payload = JWT::decode($jwt_token, $hash_key, ['HS256']);
        } catch (ExpiredException $e) {
            self::error(
                new HttpException(Err::AUTH_JWT_EXPIRED, 'JWT is Expired: ' . $e->getMessage()),
                $throw_exception_on_error
            );
            return false;
        } catch (SignatureInvalidException $e) {
            self::error(
                new HttpException(
                    Err::AUTH_JWT_SIGNATURE_INVALID,
                    'JWT signature is invalid: ' . $e->getMessage()
                ),
                $throw_exception_on_error
            );
            return false;
        } catch (BeforeValidException $e) {
            self::error(
                new HttpException(
                    Err::AUTH_JWT_BEFORE_VALID,
                    'JWT Before valid: ' . $e->getMessage()
                ),
                $throw_exception_on_error
            );
            return false;
        } catch (\Exception $e) {
            self::error(
                new HttpException(Err::AUTH_JWT_INVALID, 'JWT Invalid: ' . $e->getMessage()),
                $throw_exception_on_error
            );
            return false;
        }

        if (!isset(self::$jwt_payload)) {
            self::error(
                new HttpException(Err::AUTH_UNKNOWN, 'JWT Unknown error: decoded token is null'),
                $throw_exception_on_error
            );
            return false;
        }

        return true;
    }

    /**
     * Getting processed and check jwt token
     * @return stdClass
     * @throws ErrorException
     */
    public static function getPayload()
    {
        L::d("Get jwt payload: " . json_encode(self::$jwt_payload));
        return self::$jwt_payload;
    }
}
