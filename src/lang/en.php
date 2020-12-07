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

return [

    // Message
    'm00000' => 'mirage',

    // Success
    's00000' => 'success',

    // General Dev Error
    'f00000' => 'unknown server error',

    // Request
    'f00100' => 'unknown request error',
    'f00101' => 'request is invalid',
    'f00102' => 'inputs are invalid',
    'f00103' => 'there is no response for you request',

    // Response
    'f00200' => 'unknown response error',
    'f00201' => 'response template is invalid',

    // Database
    'f00300' => 'unknown database error',
    'f00301' => 'database saving action error',

    // Authentication
    'f00400' => 'unknown authentication error',
    'f00401' => 'invalid username or password',
    'f00402' => 'invalid account type',
    'f00403' => 'login failed',
    'f00404' => 'auth token not found',
    'f00405' => 'auth token is invalid',
    'f00406' => 'auth token is invalid',
    'f00407' => 'auth token signature is invalid',
    'f00408' => 'auth token before validation error',
    'f00409' => 'auth header is empty',
    'f00410' => 'auth header not found',
    'f00411' => 'auth token after validation error',
    'f00412' => 'auth token invalid payload',
    'f00413' => 'database saving action error',

    // Acl
    'f00500' => 'unknown acl error',
    'f00501' => 'access denied',
    'f00502' => 'token not found or invalid',
    'f00503' => 'role name in invalid',

    // Request Hash
    'f00600' => 'unknown request hash error',
    'f00601' => 'hash key is invalid',
    'f00602' => 'hash version is invalid',
    'f00603' => 'hash time is invalid',
    'f00604' => 'hash random is invalid',
    'f00605' => 'hash token is invalid',
    'f00606' => 'hash client token is invalid',
    'f00607' => 'hash tokens are not identical',
    'f00608' => 'hash duplication',
    'f00609' => 'hash IP call limit reaches',
];
