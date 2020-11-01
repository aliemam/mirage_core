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
use Mirage\Libs\Middleware;
use Mirage\Libs\Route;
use phpDocumentor\Plugin\Scrybe\Converter\ToHtmlInterface;

/**
 * Class RoutesCollection
 * @package Mirage
 */
class RoutesCollection extends \Phalcon\Mvc\Micro\Collection
{
    public bool $enabled = true;
    private string $collection_handler;
    private string $collection_prefix;
    private array $collection_routes;
    private static array $collections;

    public static function boot(): void
    {
        $called_class = get_called_class();
        $collection = new $called_class();
        $collection->setlazy(false);
        $collection_prefix = $collection->getCollectionPrefix();
        $collection_handler = $collection->getCollectionHandler();
        $collection_routes = $collection->getCollectionRoutes();
        if (!isset($collection_prefix)) {
            throw new ErrorException("[ERROR][100] Collection with no prefix has error: 
            prefix should be defined.");
        }
        if (!isset($collection_handler)) {
            throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                handler should be defined.");
        }
        $collection_routes = $collection_routes ?? [];
        foreach ($collection_routes as $collection_route) {
            $route_path = $collection_route['path'];
            $route_method = $collection_route['method'];
            $route_action = $collection_route['action'];
            $route_name = $collection_route['name'] ?? '';
            $route_middlewares = $collection_route['middlewares'] ?? [];
            $route_accesses = $collection_route['accesses'] ?? [];

            if (!isset($route_path)) {
                throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    path should be defined.");
            }
            if (!isset($route_method)) {
                throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    method should be defined.");
            }
            if (!isset($route_action)) {
                throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    action should be defined.");
            }
            if (!in_array($route_method, Route::METHODS)) {
                throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    method $route_method is not valid.");
            }

            foreach ($route_middlewares as $route_middleware) {
                if (!$route_middleware instanceof Middleware) {
                    throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    Route with path $route_path has invalid middleware.");
                }
            }

            $route_path = "$collection_prefix/$route_path";
            if (isset(self::$collections["$route_method:$route_path"])) {
                throw new ErrorException("[ERROR][100] Collection with prefix $collection_prefix has error: 
                    prefix $collection_prefix could not be duplicated.");
            }

            $methods = strtolower($route_method);
            $methods = explode(',', $methods);
            foreach ($methods as $method) {
                $collection->{$method}(
                    $collection_route['path'],
                    $collection_route['action']
                );
                self::$collections["$route_method:$route_path"]
                    = new Route(
                        $route_path,
                        $route_method,
                        $route_action,
                        $route_name,
                        $route_middlewares,
                        $route_accesses
                    );
            }
        }
    }


    /**
     * Get the value of collection_handler
     */
    public function getCollectionHandler(): string
    {
        return $this->collection_handler;
    }

    /**
     * Set the value of collection_handler
     *
     * @param Controller $collection_handler
     * @return $this
     */
    protected function setCollectionHandler(Controller $collection_handler): self
    {
        $this->collection_handler = $collection_handler;
        $this->setHandler($collection_handler);

        return $this;
    }

    /**
     * Get the value of collection_prefix
     *
     * @return string
     */
    public function getCollectionPrefix(): string
    {
        return $this->collection_prefix;
    }

    /**
     * Set the value of collection_prefix
     *
     * @param string $collection_prefix
     * @return $this
     */
    public function setCollectionPrefix(string $collection_prefix): self
    {
        $this->collection_prefix = $collection_prefix;
        $this->setPrefix($collection_prefix);

        return $this;
    }

    /**
     * Get the value of collection_routes
     *
     * @return array
     */
    public function getCollectionRoutes(): array
    {
        return $this->collection_routes;
    }

    /**
     * Set the value of collection_routes
     *
     * @param array $collection_routes
     * @return $this
     */
    public function setCollectionRoutes(array $collection_routes): self
    {
        $this->collection_routes = $collection_routes;

        return $this;
    }

    /**
     * Get the value of collection_routes
     *
     * @return array
     */
    public static function getCollections(): array
    {
        return self::$collections;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Default Mirage Framework Collection
     *
     * @return RoutesCollection
     */
    public static function healthCheckCollection()
    {
        $collection = new RoutesCollection();
        $collection->setCollectionHandler('Mirage\App\Controller')
            ->setCollectionPrefix('/micro_service')
            ->setCollectionRoutes([
                [
                    'path' => '/health_check',
                    'method' => 'get,post',
                    'action' => 'checkMicroService',
                ],
            ]);

        return $collection;
    }
}
