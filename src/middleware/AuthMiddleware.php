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
use Mirage\Libs\Config;
use Mirage\Libs\Helper;
use Mirage\Libs\L;
use Mirage\App\App;
use Phalcon\Events\Event;

/**
 * Class Auth Middleware
 * @package Mirage
 */
class AuthMiddleware extends Middleware
{

    /** @var string overwriting name of middleware */
    const NAME = 'auth';

    /**
     * @param Event $event
     * @param App $app
     * @return bool
     * @throws \ErrorException
     * @throws HttpException
     */
    public function check(Event $event, App $app)
    {
        $headers = Helper::getHeaders();
        $auth_header = $headers['m-auth'] ?? null;
        L::d('M-AUTH: ' . $auth_header);
        \Mirage\Libs\Auth::checkJWTToken($auth_header);

        return true;
    }
}
