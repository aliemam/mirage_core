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

use ErrorException;
use Phalcon\Cache\Exception\InvalidArgumentException;

/**
 * Class C
 * This class is just a wrapper on Cache class to simplify calling Cache everywhere in code
 * @package Mirage\Libs
 */
final class C extends Cache
{
    /**
     * @param string $default_cache_name
     * @throws ErrorException
     */
    public static function defaultCache(string $default_cache_name): void
    {
        self::setDefaultCache($default_cache_name);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     * @throws ErrorException|InvalidArgumentException
     */
    public static function a(string $key, $value, int $expiration = 31536000): void
    {
        self::getInstance()->add($key, $value, $expiration);
    }

    /**
     * @param string $key
     * @return mixed|null
     * @throws ErrorException|InvalidArgumentException
     */
    public static function g(string $key)
    {
        return self::getInstance()->get($key);
    }

    /**
     * @param string $patter
     * @return array
     * @throws ErrorException
     */
    public static function gbp(string $patter): array
    {
        return self::getInstance()->getByPattern($patter);
    }

    /**
     * @param string $key
     * @return bool
     * @throws ErrorException|InvalidArgumentException
     */
    public static function d(string $key): bool
    {
        return self::getInstance()->delete($key);
    }

    /**
     * @param string $patter
     * @return bool
     * @throws ErrorException
     */
    public static function dbp(string $patter): bool
    {
        return self::getInstance()->deleteByPattern($patter);
    }

}