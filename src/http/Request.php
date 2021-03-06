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

use App\Constants\Err;
use App\Constants\Services;
use ErrorException;
use Mirage\Exceptions\HttpException;
use Mirage\Libs\Helper;
use Mirage\Libs\L;
use Mirage\Libs\Route;
use Phalcon\Di;
use Phalcon\Mvc\Router\RouteInterface;
use stdClass;

/**
 * Class Request
 *
 * TODO: Not Completed
 * @author Ali Emamhadi <aliemamhadi@gmail.com>
 */
final class Request extends \Phalcon\Http\Request
{
    /**
     * Get All data in request. Post data, Payload data, Query String data, ...
     *
     * @param array $params
     * @return array
     * @throws HttpException
     * @throws ErrorException
     */
    public static function getData(array $params = []): array
    {
        $data1 = (array)((new self)->getJsonRawBody() ?? []);
        $data2 = (array)((new self)->get() ?? []);

        $data = (object)array_merge((array)$data1, (array)$data2);
        L::d("Request All Data: " . json_encode($data));

        $returned_params = [];
        foreach ($params as $param => $value) {
            $req = false;
            $name = $param;
            if ($param[0] === '*') {
                $req = true;
                $name = substr($param, 1);
            }

//            L::d("checking $name, value: " . $data->$name ?? null);
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

    /**
     * Getting Information of request
     *
     * @return Route
     */
    public static function getRoute(): Route
    {
        $app = \Phalcon\Di::getDefault()->getShared(Services::MICRO);
        $collection = $app->getCollections();
        $route = $app->getRouter()->getMatchedRoute();
        $route_ids = explode('-_-', $route->getName());
        $collection_id = $route_ids[0];
        $route_id = $route_ids[1];

        return $collection[$collection_id]->getRoutes()[$route_id];
    }

    /**
     * Getting all the info around the incoming request
     *
     * @return stdClass
     */
    public static function getRequestInfo(): stdClass
    {
        $request = new self;
        $request_info = new stdClass();
        $request_info->url = $request->getURI(true);
        $request_info->header = (array)($request->getHeaders() ?? []);
        $request_info->body = (array)($request->getJsonRawBody() ?? []);
        $request_info->query = (array)($request->getQuery() ?? []);
        $request_info->post = (array)($request->getPost() ?? []);

        return $request_info;
    }
}
