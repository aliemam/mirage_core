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
use Exception;
use Phalcon\Cache\Frontend\Json as DataFrontend;
use Phalcon\Cache\Backend\Redis;

/**
 * Class Cache
 * @package Mirage\Libs
 */
final class Cache
{

    /** @var Cache Singleton instance of class Cache */
    private static ?Cache $instance = null;

    /** @var bool checks if cache should work or should be disabled */
    private bool $is_disable = false;

    /** @var Phalcon\Cache\Backend\{Driver} $cache Based on driver it could be any class type */
    private $cache;

    /** @var string Based on supported cache driver by phalcon, this variable was set in config. */
    private string $cache_driver;

    /** @var string Prefix that prepend to the key of data value, which is stored in memory */
    private string $cache_prefix = '_mirage_';

    /**
     * Cache constructor.
     * It is a Singleton class.
     * @throws Exception
     */
    private function __construct()
    {
        $this->is_disable = !Config::get('app.enable_cache');
        $this->cache_prefix = Config::get('cache.prefix');
        $this->cache_driver = Config::get('cache.driver');

        try {
            $frontCache = new DataFrontend([
                'lifetime' => 31536000
            ]);
            switch ($this->cache_driver) {
                case 'redis':
                    $this->cache = new Redis($frontCache, Config::get('cache'));
                    break;
                default:
                    throw new ErrorException("Unknown cache driver: $this->cache_driver");
                    break;
            }
        } catch (Exception $e) {
            throw new ErrorException('Cant load Cache: ' . $e->getMessage());
        }
    }

    /**
     * Get single instance of class Cache for class internal use.
     * @return Cache
     * @throws Exception
     */
    private static function getInstance(): Cache
    {
        self::$instance ??= new Cache();
        return self::$instance;
    }

    /**
     * Create object instance
     * @return Cache
     */
    public static function create(): Cache
    {
        return self::getInstance();
    }

    /**
     * @param string $key This is key of data we want to save in memory
     * @param mixed $value This is value of data we want to save in memory and will be encoded as json before saving.
     * @param string $time This parameter is English textual datetime description that
     * we want to save data before expiring it.
     * @return void
     * @throws Exception
     * @example add('a','b','2 weeks 1 day 4 hours')
     */
    public static function set(string $key, $value, string $time = '1 year'): void
    {
        $instance = self::getInstance();
        if ($instance->is_disable) {
            L::w("Cache is disable but you trying to add to it!!!");
            return;
        }
        $end = strtotime("+$time");
        if ($end === false) {
            L::e("Cant understand time: $time");
            return;
        }
        $expiration = $end - time();
        L::d("Adding to cache...");
        L::d("Key: $key");
        L::d("Value: $value");
        L::d("Second to expire: $expiration");
        $instance->cache->save($key, $value, $expiration);
    }

    /**
     * @param string $key The key of data stored in memory
     * @return null|mixed
     * @throws Exception
     */
    public static function get(string $key)
    {
        $instance = self::getInstance();
        if ($instance->is_disable) {
            L::w("Cache is disable but you trying to get form it!!!");
            return null;
        }

        $item = $instance->cache->get($key);
        if (!isset($item)) {
            L::w("Trying to get key: $key from cache which is not exist");
            return null;
        }
        L::d("cache: $key is found");

        return $item;
    }

    /**
     * Remove from memory by key.
     * This function gets pattern that could be part of any key in memory.
     * Then all data contain that pattern will be deleted from memory.
     * @param string $pattern This variable is pattern of key in memory which we want to delete its value from memory.
     * @return bool
     * @throws Exception
     */
    public static function remove(string $pattern): bool
    {
        L::d("Remove pattern: $pattern from cache");
        $instance = self::getInstance();
        if (!$instance->is_disable) {
            L::w("Cache is disable but you trying to remove data from memory!!!");
            return false;
        }
        //        foreach($cache->queryKeys($instance->prefix.$pattern) as $key){
        //            $key = substr($key, strlen($instance->prefix));
        //            $cache->delete($key);
        //        }
        $found = 0;
        foreach ($instance->cache->queryKeys() as $key) {
            if (strpos($key, $pattern) !== false) {
                $key = substr($key, strlen($instance->cache_prefix));
                L::d("Found key: $key for deleting but could not.");
                $instance->cache->delete($key);
                $found++;
            }
        }
        L::d("Found $found item to delete");
        return ($found > 0);
    }

    public function __destruct()
    {
        foreach ($this as &$value) {
            $value = null;
        }
    }
}
