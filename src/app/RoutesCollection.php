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

namespace Mirage\App;

use ErrorException;
use Mirage\Constants\Services;
use Mirage\Libs\Helper;
use Mirage\Libs\Route;
use Phalcon\Di;
use phpDocumentor\Plugin\Scrybe\Converter\ToHtmlInterface;

/**
 * Class RoutesCollection
 * @package Mirage
 */
class RoutesCollection extends \Phalcon\Mvc\Micro\Collection
{
    private array $routes = [];
    private string $id;

    public function boot(): void
    {
        foreach ($this->routes as $route) {
            if (!$route instanceof Route) {
                throw new ErrorException("[ERROR][100] invalid route object.");
            }
            $this->id = Helper::getUniqueId($this->getPrefix());
            $this->routes[$route->getId()] = $route;
            $route->setCollectionId($this->id);
            $method = $route->getMethod();
            $path = $route->getPath();
            $action = $route->getAction();
            $middlewares = $route->getMiddlewares();
            $accesses = $route->getAccesses();
            $this->{$method}($path, $action, $this->id.'-_-'.$route->getId());

            foreach ($middlewares as $middleware) {
                if(!$middleware instanceof Middleware) continue;
                $event_manager = Di::getDefault()->getShared(Services::EVENTS_MANAGER);
                $event_manager->attach(Services::MICRO, $middleware);
            }
        }
    }

    public function getId()
    {
        return Helper::getUniqueId($this->getPrefix());
    }

    /**
     * Get the value of collection_routes
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Set the value of collection_routes
     *
     * @param array $routes
     * @return void
     */
    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }
}
