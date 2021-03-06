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
 * Class L
 * This class is just a wrapper on Logger class to simplify calling logger everywhere in code
 * @package Mirage\Libs
 */
final class L extends Logger
{
    /**
     * @param $message
     * @throws ErrorException
     */
    public static function em($message): void
    {
        self::getinstance()->emergency($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function a($message): void
    {
        self::getinstance()->alert($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function c($message): void
    {
        self::getinstance()->critical($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function e($message): void
    {
        self::getinstance()->error($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function w($message): void
    {
        self::getinstance()->warning($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function n($message): void
    {
        self::getinstance()->notice($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function i($message): void
    {
        self::getinstance()->info($message);
    }

    /**
     * @param $message
     * @throws ErrorException
     */
    public static function d($message): void
    {
        self::getinstance()->debug($message);
    }
}
