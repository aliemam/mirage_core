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

use Dotenv\Dotenv;

/**
 * Class Config
 * @package Mirage\Libs
 */
final class Config
{

    /** @var Config Singleton instance of Config class */
    private static ?Config $instance = null;

    /** @var array All configs stores here */
    private array $configs = [];

    /**
     * Config constructor.
     */
    private function __construct()
    {
//        $dotenv = Dotenv::createMutable(MIRAGE_APP_DIR);
//        $dotenv->load();
        foreach (scandir(CONFIG_DIR) as $config_file) {
            $path = CONFIG_DIR . '/' . $config_file;
            $config_name = str_replace('.php', '', $config_file);
            if (is_file($path)) {
                $this->configs[$config_name] = require_once $path;
            }
        }
    }

    /**
     * Get single instance of Config class.
     * @return Config
     */
    private static function getInstance(): Config
    {
        self::$instance ??= new Config();
        return self::$instance;
    }

    /**
     * Create object instance
     * @return Config
     */
    public static function create(): Config
    {
        return self::getInstance();
    }

    /**
     * Setting parameter in config in runtime for further use.
     * This function gets $params in format of c1.c2.c3 and value of mixed.
     * Then it assigns the value parameter to config[c1][c2][c3].
     * @param string $params In config chain this param is something like co1.co2.co3... .
     * @param mixed $value The value that should save in configs
     * @return void
     */
    public static function set(string $params, $value): void
    {
        $instance = self::getInstance();
        $config = &$instance->configs;
        $params = explode('.', $params);
        foreach ($params as $param) {
            if (!isset($config[$param])) {
                L::e("config $param is not valid in chain config " . implode('.', $params));
                return;
            }
            $config = &$config[$param];
        }
        $config = $value;
    }

    /**
     * Get config.
     * This function gets $params in format of c1.c2.c3 and value of mixed. Then it returns value of config[c1][c2][c3].
     * @param string $params In config chain this param is something like co1.co2.co3... .
     * @return array|bool|mixed
     */
    public static function get(string $params)
    {
        $config = self::getInstance()->configs;
        $params = explode('.', $params);
        foreach ($params as $param) {
            if (!isset($config[$param])) {
                L::e("config $param is not valid in chain config " . implode('.', $params));
                return false;
            }
            $config = $config[$param];
        }

        return $config;
    }


    public function __destruct()
    {
        foreach ($this as &$value) {
            $value = null;
        }
    }
}
