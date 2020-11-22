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

namespace Mirage\Http;

use Mirage\App\RoutesCollection;
use Mirage\Constants\Err;
use Mirage\Exceptions\HttpException;
use Mirage\Libs\L;
use Phalcon\Mvc\Router\RouteInterface;

/**
 * Class Request
 *
 * TODO: Not Completed
 * @author Ali Emamhadi <aliemamhadi@gmail.com>
 */

final class Request extends \Phalcon\Http\Request
{
//    /**
//     * Get Matched Route Object
//     *
//     * @param \Phalcon\Mvc\Router\RouteInterface $router
//     * @return void
//     */
//    public static function getRoute(\Phalcon\Mvc\Router\RouteInterface $router)
//    {
//        $route_path = $router->getMatchedRoute()->getPattern();
//        return RoutesCollection::getCollections()[$route_path] ?? null;
//    }
//
//    /**
//     * Get Middlewares of Matched Route Object
//     *
//     * @param \Phalcon\Mvc\Router\RouteInterface $router
//     * @return void
//     */
//    public static function getMiddlewares(\Phalcon\Mvc\Router\RouteInterface $router)
//    {
//        $route_path = $router->getMatchedRoute()->getPattern();
//        $route = RoutesCollection::getCollections()[$route_path] ?? null;
//        return $route->getMiddlewares() ?? [];
//    }

    /**
     * Get All data in request. Post data, Payload data, Query String data, ...
     *
     * @param array $params
     * @return array
     */
    public static function getData(array $params = [])
    {
        $data1 = (array) ((new self)->getJsonRawBody() ?? []);
        $data2 = (array) ((new self)->get() ?? []);

        $data = (object) array_merge((array) $data1, (array) $data2);
        L::d("Request All Data: " . json_encode($data));

        $returned_params = [];
        foreach ($params as $param => $value) {
            $req = false;
            $name = $param;
            if ($param[0] === '*') {
                $req = true;
                $name = substr($param, 1);
            }

            L::d("checking $name, value: " . $data->$name);
            if ((!isset($data->$name) || $data->$name == '') && $req) {
                throw new HttpException(Err::REQUEST_MISS_PARAM, "param $name should be specified");
            }

            if ($req) {
                $returned_params[] = $data->$name;
            } else {
                $returned_params[] = (isset($data->$name) && $data->$name != '')
                    ? $data->$name : $value;
            }
        }

        return $returned_params;
    }
}
