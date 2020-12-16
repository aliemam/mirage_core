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

use Psr\Cache\CacheItemInterface;

/**
 * Class Cache
 * @package Mirage\Libs
 */
class CacheItem implements CacheItemInterface
{
    private string $key;
    private $value;
    private bool $is_hit = false;
    private $expires_at;
    private $expires_after;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get()
    {
        if (isset($this->expires_after) && $this->getExpirationInSecond() > time()) {
            $this->is_hit = true;
            return $this->value;
        }
        $this->is_hit = false;
        return null;
    }

    public function isHit(): bool
    {
        return $this->is_hit;
    }

    public function isMiss(): bool
    {
        return !$this->is_hit;
    }

    public function set($value): self
    {
        $this->value = $value;
        $this->is_hit = true;
        return $this;
    }

    public function expiresAt($expiration): self
    {
        if (isset($expiration)) {
            $exp_after = $expiration->getTimestamp() - time();
            if ($exp_after > 0) {
                $this->expiresAfter($exp_after);
            }
        }
        $this->expires_at = $expiration;
        return $this;
    }

    public function expiresAfter($time): int
    {
        $this->expires_after = $time;
        return $time;
    }

    public function getExpirationInSecond()
    {
        $seconds = $this->expires_after;
        if ($this->expires_after instanceof \DateInterval) {
            $seconds = $this->expires_after->y * 31536000 + $this->expires_after->m * 2628000
                + $this->expires_after->d * 86400 + $this->expires_after->h * 3600 + $this->expires_after->i * 60
                + $this->expires_after->s;
        }
        return $seconds;
    }
}
