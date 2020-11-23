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
use Phalcon\Logger as PhalconLogger;
use Psr\Log\LoggerInterface;
use Phalcon\Logger\Adapter\Stream;

/**
 * Class Logger
 * @package Mirage\Libs
 */
class Logger implements LoggerInterface
{
    /**
     * @var array of Logger Objects that store here as Singleton Object
     */
    private static array $loggers = [];

    /**
     * This is a default logger to be called if nothing passed
     * @var string each logger has name that can be identified by that name,
     */
    private static string $default_logger_name = 'mirage';

    /**
     * @var array each logger has config.
     */
    private static array $default_logger_config = [];

    /**
     * @var string each logger has name that can be identified by that name
     */
    private string $logger_name;

    /** @var PhalconLogger Phalcon Logger object that actually handles logging process */
    private PhalconLogger $logger;

    /**
     * @var array stores logger config which needed to connect to cache driver
     */
    private array $logger_config;
    
    /**
     * Value will be prepended to message before logging. the full message would be "$prefix$message"
     * @var string
     */
    private string $prefix;

    /**
     * Logger constructor.
     * @param string $logger_name
     * @throws ErrorException
     */
    private function __construct(string $logger_name, array $logger_config)
    {
        $this->logger_name = $logger_name;
        $this->logger_config = $logger_config;
        try {
            $path = $logger_config['path'] ?? LOG_DIR;
            $tag = $logger_config['tag'] ?? 'NT';
            $path .= '/' . $logger_name . '_' . date('Y-m-d', time()) . '.log';
//            fopen($path, 'a+');
//            chmod($path, 0777);

            $adapter = new Stream($path);
            $this->logger = new PhalconLogger('message', ['main' => $adapter]);
            $this->logger->setLogLevel($logger_config['level'] ?? PhalconLogger::DEBUG);

            $tracker = Config::get('app.request_tracker') ?? time();
            $ip = php_sapi_name() != "cli" ? 'cli_mode' : Helper::getIP();
            $route = $_SERVER['REQUEST_URI'] ?? 'not_http_request';
            $this->prefix = "[$tracker][$tag][$ip][$route]";
        } catch (\Exception $e) {
            throw new ErrorException('Cant create Logger: ' . $logger_name . ' :' . $e->getMessage());
        }
    }

    /**
     * Get single instance of Logger Object
     * @param string|null $logger_name
     * @param array|null $logger_config
     * @return Logger
     * @throws ErrorException
     */
    public static function getInstance(?string $logger_name = null, ?array $logger_config = null): Logger
    {
        $logger_name ??= self::$default_logger_name;
        $logger_config ??= self::$default_logger_config;
        if (!isset(self::$loggers[$logger_name])) {
            self::$loggers[$logger_name] = new Logger($logger_name, $logger_config);
        }
        return self::$loggers[$logger_name];
    }

    /**
     * Set default logger. After this each time L class called it would use this logger.
     * @param string|null $logger_name
     */
    public static function setDefaultLogger(?string $logger_name = null): void
    {
        if(!isset(self::$loggers[$logger_name])){
            throw new ErrorException("There is no logger name: $logger_name");
        }
        self::$default_logger_name = $logger_name ?? 'mirage';
    }

    /**
     * This function just reduce length of message
     * @param $msg
     * @return string
     * @throws ErrorException
     */
    private function shortMsg($msg): string
    {
        if (!isset($this->logger_config['max_length'])) {
            return $msg;
        } else {
            return substr($msg, 0, $this->logger_config['max_length']);
        }
    }

    /**
     * @param string $logger_name
     * @return Logger
     */
    public function setLoggerName(string $logger_name): self
    {
        $this->logger_name = $logger_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoggerName(): string
    {
        return $this->logger_name;
    }

    /**
     * @param array $logger_config
     * @return Logger
     */
    public function setLoggerConfig(array $logger_config): self
    {
        $this->logger_config = $logger_config;

        return $this;
    }

    /**
     * @return array
     */
    public function getLoggerConfig(): string
    {
        return $this->logger_config;
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function emergency($msg, array $arr = []): void
    {
        $this->logger->emergency($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function alert($msg, array $arr = []): void
    {
        $this->logger->alert($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function critical($msg, array $arr = []): void
    {
        $this->logger->critical($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function error($msg, array $arr = []): void
    {
        $this->logger->error($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function warning($msg, array $arr = []): void
    {
        $this->logger->warning($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function notice($msg, array $arr = []): void
    {
        $this->logger->notice($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function info($msg, array $arr = []): void
    {
        $this->logger->info($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function debug($msg, array $arr = []): void
    {
        $this->logger->debug($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param mixed $level
     * @param string $msg
     * @param array $arr
     * @throws ErrorException
     */
    public function log($level, $msg, array $arr = []): void
    {
        switch ($level) {
            case PhalconLogger::EMERGENCY:
                $this->emergency($msg, $arr);
                break;
            case PhalconLogger::ALERT:
                $this->alert($msg, $arr);
                break;
            case PhalconLogger::CRITICAL:
                $this->critical($msg, $arr);
                break;
            case PhalconLogger::ERROR:
                $this->error($msg, $arr);
                break;
            case PhalconLogger::WARNING:
                $this->warning($msg, $arr);
                break;
            case PhalconLogger::NOTICE:
                $this->notice($msg, $arr);
                break;
            case PhalconLogger::INFO:
                $this->info($msg, $arr);
                break;
            default:
                $this->debug($msg, $arr);
                break;
        }
    }

    /**
     * This function create cache for all cache config in the startup
     * @throws ErrorException
     */
    public static function boot(): void
    {
        if (defined('CONFIG_DIR') && file_exists(CONFIG_DIR . '/logger.php')) {
            $loggers = require CONFIG_DIR . '/logger.php';
            $loggers = array_reverse($loggers);
            foreach ($loggers as $logger_name => $logger_config) {
                self::$default_logger_name = $logger_name;
                self::$default_logger_config = $logger_config;
                self::getInstance($logger_name, $logger_config);
            }
        }
    }
}
