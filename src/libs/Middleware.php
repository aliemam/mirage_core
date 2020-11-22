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

use Mirage\Http\Request;
use Mirage\RestApp;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * Class Auth
 * For any middleware you want to implement, you should extend that middleware from this class.
 * This class register all middlewares in RestApi app object.
 * @package Mirage
 */
class Middleware implements MiddlewareInterface
{

    /** @var this parameter collects every needed info about called route */
    protected $route_info;

    /**
     * This function check the middleware event before phalcon execute route controller
     * @param Event $event
     * @param RestApp $app
     * @return bool
     * @throws \ErrorException
     */
    public function beforeExecuteRoute(Event $event, RestApp $app)
    {
        foreach (Request::getMiddlewares($app->router) as $middleware) {
            if ($middleware->check() === false && $middleware::TERMINATE) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Event $event
     * @param RestApi $app
     * @return bool
     * @throws \ErrorException
     */
    protected function check(Event $event, RestApi $app)
    {
        L::e('You should overwrite method check in your middleware');
        return false;
    }

    /**
     * @param RestApi $app
     * @return bool
     */
    public function call(RestApi $app)
    {
        return true;
    }
}
