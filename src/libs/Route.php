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

/**
 * Class Route
 *
 * @package Mirage\Libs
 */
class Route
{
    /** constant http allowed methods */
    const METHODS = ['get', 'post', 'put', 'options', 'delete', 'head', 'connect', 'patch', 'purge', 'trace'];

    /** @var string uri path which will be resolved by this route */
    private string $path;

    /** @var string of http method */
    private string $method;

    /** @var string function in controller triggered by this route */
    private string $action;

    /** @var string id of route */
    private string $id;

    /** @var string id of route collection */
    private string $collection_id;

    /** @var array route middlewares as each element should be instance of Mirage/Libs/Middleware class */
    private array $middlewares = [];

    /** @var array route accesses */
    private array $accesses = [];

    /**
     * Route constructor function.
     *
     * @param string $path
     * @param string $method
     * @param string $action
     * @param array $middlewares
     * @param array $accesses
     */
    public function __construct(
        string $path,
        string $method,
        string $action,
        array $middlewares = [],
        array $accesses = []
    )
    {
        $this->id = Helper::getUniqueId($path, $method, $action);
        $this->path = $path;
        $this->method = $method;
        $this->action = $action;
        $this->middlewares = $middlewares;
        $this->accesses = $accesses;
    }

    /**
     * Get the value of path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the value of method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get the value of action
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get the value of id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the value of middlewares
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get the value of accesses
     *
     * @return array
     */
    public function getAccesses(): array
    {
        return $this->accesses;
    }

    /**
     * Get the Route Collection Id
     *
     * @return string
     */
    public function getCollectionId(): string
    {
        return $this->collection_id;
    }

    /**
     * Set the Route Collection Id
     *
     * @return string
     */
    public function setCollectionId($collection_id): void
    {
        $this->collection_id = $collection_id;
    }
}
