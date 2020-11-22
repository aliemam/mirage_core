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
use Phalcon\Cache\CacheFactory;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Storage\Adapter\Redis;

/**
 * Class Cache
 * @package Mirage\Libs
 */
class Cache
{
    /** @var array of Cache Objects that store here as Singleton Object */
    private static array $caches = [];

    /**
     * @var string each cache has name that can be identified by that name,
     * This is a default cache to be called if nothing passed
     */
    private static string $default_cache_name = 'mirage';

    /**
     * @var array each cache has config.
     */
    private static array $default_cache_config = [];

    /**
     * @var Cache Phalcon Logger object that actually handles logging process
     */
    private Cache $cache;

    /**
     * @var string each cache has name that can be identified by that name
     */
    private string $cache_name;

    /**
     * @var array stores cache config which needed to connect to cache driver
     */
    private array $cache_config;

    /**
     * Cache constructor.
     * @param string $cache_name
     * @param array $cache_config
     * @throws ErrorException
     */
    private function __construct(string $cache_name, array $cache_config)
    {
        try {
            if (!isset($cache_config['adapter']) ||
                !in_array($cache_config['adapter'], ['redis', 'memcached', 'memory'])) {
                throw new ErrorException('Driver should be specified [redis|memcached|memory]');
            }
            if (!isset($cache_config['serializer']) ||
                !in_array(
                    $cache_config['serializer'],
                    ['base64', 'Base64', 'igbinary', 'Igbinary', 'json', 'Json', 'msgpack', 'Msgpack', 'php', 'Php']
                )
            ) {
                $cache_config['serializer'] = 'none';
            }
            $cache_config['defaultSerializer'] = ucfirst($cache_config['serializer']);
            $adapter_factory = new AdapterFactory(
                new SerializerFactory(),
                [
                    'defaultSerializer' => $cache_config['defaultSerializer'],
                    'lifetime' => $cache_config['lifetime'] ?? 31536000
                    
                ]
            );
            $cache_factory = new CacheFactory($adapter_factory);
            $cache_config['prefix'] .= $cache_name;
            $this->cache = $cache_factory->load($cache_config);
            $this->cache_name = $cache_name;
            $this->cache_config = $cache_config;
        } catch (\Exception $e) {
            throw new ErrorException('Cant create Cache: ' . $cache_name . ' :' . $e->getMessage());
        }
    }

    /**
     * Get single instance of Cache Object
     * @param string|null $cache_name
     * @param array|null $cache_config
     * @return Cache
     * @throws ErrorException
     */
    public static function getInstance(?string $cache_name = null, ?array $cache_config = null): Cache
    {
        $cache_name ??= self::$default_cache_name;
        $cache_config ??= self::$default_cache_config;
        if (!isset(self::$caches[$cache_name])) {
            self::$caches[$cache_name] = new Cache($cache_name, $cache_config);
        }
        return self::$caches[$cache_name];
    }

    /**
     * Set default cache. After this each time C class called, it will use this cache.
     * @param string|null $cache_name
     */
    public static function setDefaultCache(string $cache_name = null): void
    {
        self::$default_cache_name = $cache_name ?? 'mirage';
    }

    /**
     * @param string $cache_name
     * @return Cache
     */
    public function setCacheName(string $cache_name): self
    {
        $this->cache_name = $cache_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheName(): string
    {
        return $this->cache_name;
    }

    /**
     * @param array $cache_config
     * @return Cache
     */
    public function setCacheConfig(array $cache_config): self
    {
        $this->cache_config = $cache_config;

        return $this;
    }

    /**
     * @return array
     */
    public function getCacheConfig(): array
    {
        return $this->cache_config;
    }

    /**
     * @param string $key This is key of data we want to save in memory
     * @param mixed $value This is value of data we want to save in memory and will be encoded as json before saving.
     * @param int $expiration in seconds
     * @return void
     * @throws ErrorException
     */
    public function add(string $key, $value, int $expiration = 31536000): void
    {
        if (!Config::get('app.cache_enable')) {
            L::w("Cache is disable but you trying to, can not add!!!");
            return;
        }
        $now = time();
        L::d("Adding to cache...");
        L::d("Key: $key");
        L::d("Value: $value");
        L::d("Adding time: " . $now);
        L::d("Second to expire: $expiration");
        $data = new \stdClass();
        $data->data = $value;
        $data->time = $now;
        $this->cache->save($key, $data, $expiration);
    }

    /**
     * @param string $key The key of data stored in memory
     * @return null|mixed
     * @throws ErrorException
     */
    public function get(string $key)
    {
        if (!Config::get('app.cache_enable')) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return null;
        }

        $data = $this->cache->get($key);
        if (!isset($data)) {
            L::w("Trying to get key: $key from cache which is not exist");
            return null;
        }
        L::d("cache: $key is found");

        return $data->data;
    }

    /**
     * This function gets pattern that could be part of any key in memory.
     * @param string $pattern The pattern of key of data stored in memory
     * @return array
     * @throws ErrorException
     */
    public function getByPattern(string $pattern): array
    {
        if (!Config::get('app.cache_enable')) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return [];
        }

        $data = $this->cache->getKeys($pattern);
        if (count($data)) {
            L::w("Trying to get data by pattern: $pattern from cache which is not exist");
            return [];
        }
        $results = [];
        foreach ($data as $d) {
            $results[] = $d->data;
        }
        L::d("cache: $pattern found ".count($results). " results");

        return $results;
    }

    /**
     * @param string $key The key of data stored in memory
     * @return bool
     * @throws ErrorException
     */
    public function delete(string $key): bool
    {
        if (!Config::get('app.cache_enable')) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return false;
        }

        $bool = $this->cache->delete($key);
        if (!$bool) {
            L::w("Trying to delete key: $key from cache which is not exist");
        }

        return $bool;
    }

    /**
     * This function deletes pattern that could be part of any key in memory.
     * @param string $pattern The pattern of key of data stored in memory
     * @return bool
     * @throws ErrorException
     */
    public function deleteByPattern(string $pattern): bool
    {
        if (!Config::get('app.cache_enable')) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return false;
        }

        $data = $this->cache->getKeys($pattern);
        if (count($data)) {
            L::w("Trying to delete data by pattern: $pattern from cache which is not exist");
            return false;
        }
        $results = true;
        foreach ($data as $d) {
            if (!$d->delete()) {
                $results = false;
            };
        }

        return $results;
    }

    /**
     * This function create cache for all cache config in the startup
     * @throws ErrorException
     */
    public static function boot(): void
    {
        if (defined('CONFIG_DIR')) {
            $caches = require_once CONFIG_DIR . '/cache.php';
            foreach ($caches as $cache_name => $cache_config) {
                self::$default_cache_name ??= $cache_name;
                self::$default_cache_config ??= $cache_config;
                self::getInstance($cache_name, $cache_config);
            }
        }
    }
}
