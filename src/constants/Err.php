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


namespace App\Constants;

class Err
{
    // General Dev Error
    const GENERAL_UNKNOWN = 'f00000-503';

    // Request
    const REQUEST_UNKNOWN = 'f00100-400';
    const REQUEST_INVALID = 'f00101-400';
    const REQUEST_MISS_PARAM = 'f00102-400';
    const REQUEST_NOT_FOUND ='f00103-404';

    // Response
    const RESPONSE_UNKNOWN = 'f00200-503';
    const RESPONSE_WRONG_PARAM = 'f00201-503';

    // Database
    const DATABASE_UNKNOWN = 'f00300-500';
    const DATABASE_SAVE = 'f00301-503';

    // Authentication
    const AUTH_UNKNOWN = 'f00400-401';
    const AUTH_INVALID_USERNAME_PASSWORD = 'f00401-403';
    const AUTH_INVALID_ACCOUNT_TYPE = 'f00402-403';
    const AUTH_LOGIN_FAILED = 'f00403-403';
    const AUTH_TOKEN_MISS_PARAM = 'f00404-403';
    const AUTH_JWT_INVALID = 'f00405-403';
    const AUTH_JWT_EXPIRED = 'f00406-403';
    const AUTH_JWT_SIGNATURE_INVALID= 'f00407-403';
    const AUTH_JWT_BEFORE_VALID = 'f0048-403';
    const AUTH_HEADER_EMPTY = 'f00409-401';
    const AUTH_HEADER_NOT_FOUND = 'f00410-401';
    const AUTH_JWT_AFTER_VALID = 'f00411-403';
    const AUTH_TOKEN_INVALID_PAYLOAD = 'f00412-403';
    const AUTH_JWT_HASH_KEY_NOT_EXIST = 'f00413-403';

    // Acl
    const ACL_UNKNOWN = 'f00500-403';
    const ACL_ACCESS_DENIED = 'f00501-403';
    const ACL_CANT_FIND_TOKEN = 'f00502-403';
    const ACL_CANT_FIND_ROLE_NAME = 'f00503-403';

    // RequestHash
    const REQUEST_HASH_UNKNOWN = 'f00600-403';
    const REQUEST_HASH_KEY_NOT_FOUND = 'f00601-403';
    const REQUEST_HASH_VERSION_NOT_FOUND = 'f00602-403';
    const REQUEST_HASH_TIME_NOT_FOUND = 'f00603-403';
    const REQUEST_HASH_RANDOM_NOT_FOUND = 'f00604-403';
    const REQUEST_HASH_TOKEN_INVALID = 'f00605-403';
    const REQUEST_HASH_CLIENT_TOKEN_INVALID = 'f00606-403';
    const REQUEST_HASH_TOKENS_NOT_IDENTICAL = 'f00607-403';
    const REQUEST_HASH_DUPLICATED = 'f00608-403';
    const REQUEST_HASH_IP_CALL_LIMIT = 'f00609-403';
}
