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
use Phalcon\Logger as PhalconLogger;
use Psr\Log\LoggerInterface;
use Phalcon\Logger\Adapter\Stream;

/**
 * Class Logger
 * @package Mirage\Libs
 */
class Logger implements LoggerInterface
{
    public static bool $enable = true;

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
     * This value should be set anytime that a new request came
     * @var string
     */
    private string $tracker;

    /**
     * This value should be set anytime that a new request came
     * @var string
     */
    private string $tag;

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
     * @param array $logger_config
     * @throws ErrorException
     */
    private function __construct(string $logger_name, array $logger_config)
    {
        $this->logger_name = $logger_name;
        $this->logger_config = $logger_config;
        try {
            $this->tracker = $logger_config['tracker'] ?? 'TT';
            $this->tag = $logger_config['tag'] ?? 'NT';
            $ip = Helper::getIP();
            $route = $_SERVER['REQUEST_URI'] ?? 'not_http_request';
            $this->prefix = "[$ip][$route] ";
            if($logger_config['type'] == 'json') {
                $formatter = new \Phalcon\Logger\Formatter\Json('Y-m-d H:i:s');
            } else {
                $formatter = new \Phalcon\Logger\Formatter\Line('[%date%][%type%]%message%');
                $formatter->setDateFormat('Y-m-d H:i:s');
            }

            $path = $logger_config['path'] ?? 'file://'.LOG_DIR.'/mirage';
            if(substr($path,0,4) === 'file') {
                if (substr($path, strlen($path) - 4) === '.log') {
                    $path = substr($path,0,-4) . '_' . date('Y-m-d', time()) . '.log';
                } else {
                    $path .= '_' . date('Y-m-d', time()) . '.log';
                }
                $fp = fopen($path, 'a+');
                fclose($fp);
                @chmod($path, 0777);
            }

            $adapter = new Stream($path);
            $adapter->setFormatter($formatter);
            $this->logger = new PhalconLogger('message', ['main' => $adapter]);
            $this->logger->setLogLevel($logger_config['level'] ?? PhalconLogger::DEBUG);
        } catch (Exception $e) {
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
     * @throws ErrorException
     */
    public static function setDefaultLogger(?string $logger_name = null): void
    {
        if (!isset(self::$loggers[$logger_name])) {
            throw new ErrorException("There is no logger name: $logger_name");
        }
        self::$default_logger_name = $logger_name ?? 'mirage';
    }

    /**
     * This function just reduce length of message
     * @param $msg
     * @return string
     */
    private function shortMsg($msg): string
    {
        if (isset($this->logger_config['max_length'])) {
            $msg = substr($msg, 0, $this->logger_config['max_length']);
        }

        return "[{$this->tracker}][{$this->tag}]{$this->prefix}{$msg}";
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
    public function getLoggerConfig(): array
    {
        return $this->logger_config;
    }

    /**
     * @param string|null $tracker
     * @return void
     */
    public function setLoggerTracker(?string $tracker = null): void
    {
        $this->tracker = $tracker ?? $this->tracker;
    }

    /**
     * @return string
     */
    public function getLoggerTracker(): string
    {
        return $this->tracker;
    }

    /**
     * @param string|null $tag
     * @return void
     */
    public function setLoggerTag(?string $tag = null): void
    {
        $this->tag = $tag ?? $this->tracker;
    }

    /**
     * @return string
     */
    public function getLoggerTag(): string
    {
        return $this->tag;
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function emergency($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->emergency($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function alert($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->alert($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function critical($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->critical($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function error($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->error($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function warning($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->warning($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function notice($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->notice($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function info($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->info($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $msg
     * @param array $arr
     */
    public function debug($msg, array $arr = []): void
    {
        if (self::$enable) {
            $this->logger->debug($this->shortMsg($msg));
        }
    }

    /**
     * @param mixed $level
     * @param mixed $msg
     * @param array $arr
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
