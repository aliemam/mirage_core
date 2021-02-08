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
use Mirage\Exceptions\CacheException;
use Phalcon\Cache\CacheFactory;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Cache as PhalconCache;
use Phalcon\Cache\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Cache
 * @package Mirage\Libs
 */
class Cache implements CacheItemPoolInterface
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
     * @var PhalconCache Phalcon Cache object that actually handles logging process
     */
    private PhalconCache $cache;

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
                throw new ErrorException('Adapter should be specified [redis|memcached|memory]');
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
            $cache_config['prefix'] = $cache_name . $cache_config['prefix'];
            $adapter_factory = new AdapterFactory(
                new SerializerFactory(),
                [
                    'defaultSerializer' => $cache_config['defaultSerializer'],
                    'lifetime' => $cache_config['lifetime'] ?? 31536000

                ]
            );
            unset($cache_config['defaultSerializer']);
            $cache_factory = new CacheFactory($adapter_factory);
            $this->cache = $cache_factory->load([
                'adapter' => $cache_config['adapter'],
                'options' => $cache_config
            ]);
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
     * @throws ErrorException
     */
    public static function setDefaultCache(string $cache_name = null): void
    {
        if (!isset(self::$caches[$cache_name])) {
            throw new ErrorException("There is no cache name: $cache_name");
        }
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
     * @return bool
     * @throws ErrorException|InvalidArgumentException
     */
    public function addData(string $key, $value, int $expiration = 31536000): bool
    {
        if (isset($this->cache_config['enable']) && $this->cache_config['enable'] === false) {
            L::w("Cache is disable but you trying to, can not add!!!");
            return false;
        }
        $now = time();
        L::d("Adding to cache...");
        L::d("Key: $key");
//        L::d("Value(json_encoded to show): " . json_encode($value));
        L::d("Adding time: " . $now);
        L::d("Second to expire: $expiration");
        $data = new \stdClass();
        $data->data = $value;
        $data->time = $now;
        $data->ttl = $expiration;
        return $this->cache->set($key, $data, $expiration);
    }
    public static function add(string $cache_name, string $key, $value, int $expiration = 31536000): void
    {
        if(!isset(self::$caches[$cache_name])) {
            throw new CacheException("There is no cache name: $cache_name");
        }
        self::$caches[$cache_name]->addData($key, $value, $expiration);
    }

    /**
     * @param string $key The key of data stored in memory
     * @return null|mixed
     * @throws ErrorException|InvalidArgumentException
     */
    public function getData(string $key)
    {
        if (isset($this->cache_config['enable']) && $this->cache_config['enable'] === false) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return null;
        }

        $data = $this->cache->get($key);
        if ($data === null) {
            L::w("Trying to get key: $key from cache which is not exist");
            return null;
        }
        L::d("cache: $key is found");

        return $data->data;
    }
    public static function get(string $cache_name, string $key)
    {
        if(!isset(self::$caches[$cache_name])) {
            throw new CacheException("There is no cache name: $cache_name");
        }
        return self::$caches[$cache_name]->getData($key);
    }

    /**
     * This function gets pattern that could be part of any key in memory.
     * @param string $pattern The pattern of key of data stored in memory
     * @return array
     * @throws ErrorException
     */
    public function getDataByPattern(string $pattern): array
    {
        if (isset($this->cache_config['enable']) && $this->cache_config['enable'] === false) {
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
        L::d("cache: $pattern found " . count($results) . " results");

        return $results;
    }
    public static function getByPattern(string $cache_name, string $patter): array
    {
        if(!isset(self::$caches[$cache_name])) {
            throw new CacheException("There is no cache name: $cache_name");
        }
        return self::$caches[$cache_name]->getDataByPattern($patter);
    }

    /**
     * @param string $key The key of data stored in memory
     * @return bool
     * @throws ErrorException|InvalidArgumentException
     */
    public function deleteData(string $key): bool
    {
        if (isset($this->cache_config['enable']) && $this->cache_config['enable'] === false) {
            L::w("Cache is disable but you trying to, can not get!!!");
            return false;
        }

        $bool = $this->cache->delete($key);
        if (!$bool) {
            L::w("Trying to delete key: $key from cache which is not exist");
        }

        return $bool;
    }
    public static function delete(string $cache_name, string $key): bool
    {
        if(!isset(self::$caches[$cache_name])) {
            throw new CacheException("There is no cache name: $cache_name");
        }
        return self::$caches[$cache_name]->deleteData($key);
    }

    /**
     * This function deletes pattern that could be part of any key in memory.
     * @param string $pattern The pattern of key of data stored in memory
     * @return bool
     * @throws ErrorException
     */
    public function deleteDataByPattern(string $pattern): bool
    {
        if (isset($this->cache_config['enable']) && $this->cache_config['enable'] === false) {
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
    public static function deleteByPattern(string $cache_name, string $patter): bool
    {
        if(!isset(self::$caches[$cache_name])) {
            throw new CacheException("There is no cache name: $cache_name");
        }
        return self::$caches[$cache_name]->deleteDataByPattern($patter);
    }

    /**
     * This function create cache for all cache config in the startup
     * @throws ErrorException
     */
    public static function boot(): void
    {
        if (defined('CONFIG_DIR') && file_exists(CONFIG_DIR . '/cache.php')) {
            $caches = require CONFIG_DIR . '/cache.php';
            $caches = array_reverse($caches);
            foreach ($caches as $cache_name => $cache_config) {
                self::$default_cache_name = $cache_name;
                self::$default_cache_config = $cache_config;
                self::getInstance($cache_name, $cache_config);
            }
        }
    }

    // IMPLEMENTING THE INTERFACE

    public function getItem($key)
    {
        return $this->getData($key);
    }

    public function getItems(array $keys = array())
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->getItem($key);
        }

        return $result;
    }

    public function hasItem($key)
    {
        return $this->cache->has($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }

    public function deleteItem($key)
    {
        return $this->delete($key);
    }

    public function deleteItems(array $keys)
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function save(CacheItemInterface $item)
    {
        return $this->addData($item->getKey(), $item);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return false;
    }

    public function commit()
    {
        return false;
    }
}
