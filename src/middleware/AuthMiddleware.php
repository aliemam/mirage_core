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

use Mirage\Exceptions\HttpException;
use Mirage\Interfaces\MiddlewareInterface;
use Mirage\Libs\Config;
use Mirage\Libs\Helper;
use Mirage\Libs\L;
use Mirage\App\App;
use Mirage\RestApp;
use Phalcon\Events\Event;

/**
 * Class Auth Middleware
 * @package Mirage
 */
class AuthMiddleware implements MiddlewareInterface
{

    /**
     * This function must throw HttpException on any errors.
     *
     * @return void
     * @throw HttpException
     */
    function check(): void
    {
        L::d("CHECKING AUTH MIDDLEWARE");
        $headers = Helper::getHeaders();
        $auth_header = $headers['m-auth'] ?? null;
        L::d('M-AUTH: ' . $auth_header);
        \Mirage\Libs\Auth::checkToken($auth_header, true);
    }
}
