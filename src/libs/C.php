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

/**
 * Class C
 * This class is just a wrapper on Cache class to simplify calling Cache everywhere in code
 * @package Mirage\Libs
 */
final class C extends Cache
{
    public static function defaultCache(string $default_cache_name)
    {
        self::setDefaultCache($default_cache_name);
    }

    public static function a(string $key, $value, string $time = '1 year')
    {
        self::getInstance()->add($key, $value, $time);
    }

    public static function g(string $key)
    {
        self::getInstance()->get($key);
    }

    public static function gbp(string $patter)
    {
        self::getInstance()->getByPattern($patter);
    }

    public static function d(string $key)
    {
        self::getInstance()->delete($key);
    }

    public static function dbp(string $patter)
    {
        self::getInstance()->deleteByPattern($patter);
    }

}
