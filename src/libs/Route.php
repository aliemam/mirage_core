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
    /** @var METHODS http allowed methods */
    const METHODS = ['get', 'post', 'put', 'options', 'delete', 'head', 'connect', 'patch', 'purge', 'trace'];

    /** @var string uri path which will be resolved by this route */
    private string $path;

    /** @var string http method */
    private string $method;

    /** @var string function in controller triggered by this route */
    private string $action;

    /** @var string name of route */
    private string $name;

    /** @var array route middlewares as each element should be instance of Mirage/Libs/Middleware class */
    private array $middlewares;

    /** @var array route accesses */
    private array $accesses;

    /**
     * Route constructor function.
     *
     * @param string $path
     * @param string $method
     * @param string $action
     * @param string $name
     * @param array $middlewares
     * @param array $accesses
     */
    public function __construct(
        string $path,
        string $method,
        string $action,
        string $name,
        array $middlewares,
        array $accesses
    ) {
        $this->path = $path;
        $this->method = $method;
        $this->action = $action;
        $this->name = $name;
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
     * Get the value of name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
}
