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

namespace Mirage\Middleware;

use Mirage\Interfaces\MiddlewareInterface;
use Mirage\Libs\Config;
use Mirage\Libs\Helper;
use Mirage\Libs\L;

/**
 * Class DuplicateRequest
 * This class secures api request made by client.
 * @package Mirage
 */
class RequestHashMiddleware implements MiddlewareInterface
{

    /**
     * This function must throw HttpException on any errors.
     * In each request clients send 4 headers: "M-HASH", "M-VERSION", "M-TIME", "M-RANDOM".
     * "M-VERSION": is app version in int or string format (its a agreement between client and server side)
     * "M-TIME": is a 10 digit unix timestamp.
     * "M-RANDOM": is a random 4 digit number generated by client before making any request.
     * "M-HASH": is a sha256 hash string create by client.
     * All clients have a key that is hard coded in codes and it should be hard for hacker to find it.
     * each clients generate "M-HASH" parameter using that key and from string: "$key.$m-version.$m-"
     * 
     * @return void
     * @throw HttpException
     */
    public function check(): void
    {
        $headers = Helper::getHeaders();
        $hash = $headers['m-hash'] ?? null;
        $version = $headers['m-version'] ?? null;
        $time = $headers['m-time'] ?? null;
        $random = $headers['m-random'] ?? null;
        L::d('M-HASH: ' . $hash);
        L::d('M-VERSION: ' . $version);
        L::d('M-TIME: ' . $time);
        L::d('M-RANDOM: ' . $random);

        \Mirage\Libs\RequestHash::setKey(Config::get('app.security.'));
        \App\Libs\Hash::check($hash, $version, $time, $random);
    }
}
