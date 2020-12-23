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
use Mirage\Core;

final class Channel
{
    /**
     * Better reference for UNI_CHANNEL constance
     */
    const UNI_CHANNEL = Core::UNI_CHANNEL;

    /**
     * @var Channel singleton object
     */
    private static Channel $instance;

    /**
     * @var \parallel\Channel holds the object of universal channel
     */
    private static \parallel\Channel $channel;

    /**
     * Channel constructor.
     * @throws ErrorException
     */
    private function __construct()
    {
        try {
            self::$channel = \parallel\Channel::opne(self::UNI_CHANNEL);
        } catch (\Exception $e) {
            throw new ErrorException('Cant open universal channel :' . $e->getMessage());
        }
    }

    /**
     * returns the singleton instance
     * @return static
     */
    private static function getInstance(): self
    {
        if(!isset(self::$instance)) {
            self::$instance = new Channel();
        }
        return self::$instance;
    }

    public static function send(array $data): void
    {

    }
}