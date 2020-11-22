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
     * @var string each logger has name that can be identified by that name
     */
    private string $logger_name;

    /** @var PhalconLogger Phalcon Logger object that actually handles logging process */
    private PhalconLogger $logger;

    /**
     * This variable can be used when you want to add more data to all messages.
     * At the end each massage will be in this format:
     * "[$time][$type] [$tag][$ip][$route] $prefix$message"
     * If $tag was provided by developer. developer can track that string in logs.
     * @var string
     */
    private string $tag;

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
    private function __construct(string $logger_name)
    {
        try {
            $path = LOG_DIR . '/' . $logger_name . '_' . date('Y-m-d', time());
            $adapter = new Stream($path);
            $this->logger = new PhalconLogger('message', ['main' => $adapter]);
            switch (Config::get('app.env')) {
                case 'pro':
                    $this->logger->setLogLevel(PhalconLogger::ERROR);
                    break;
                case 'dev':
                    $this->logger->setLogLevel(PhalconLogger::DEBUG);
                    break;
            }
            chmod($path, 0777);
            $tracker = Config::get('app.request_tracker') ?? time();
            $tag = $this->tag;
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
     * @param string|null $tag
     * @return PhalconLogger
     * @throws ErrorException
     */
    public static function getInstance(?string $logger_name = null, ?string $tag = null): PhalconLogger
    {
        $logger_name ??= self::$default_logger_name;
        if (!isset(self::$loggers[$logger_name])) {
            self::$loggers[$logger_name] = new Logger($logger_name);
            self::$loggers[$logger_name]->setLoggerName($logger_name);
            self::$loggers[$logger_name]->setTag($tag ?? 'NT');
        }
        return self::$loggers[$logger_name];
    }

    /**
     * Set default logger. After this each time L class called it would use this logger.
     * @param string|null $logger_name
     */
    public static function setDefaultLogger(?string $logger_name = null): void
    {
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
        if (Config::get('app.log_mode') == 'complete') {
            return $msg;
        } else {
            return substr($msg, 0, Config::get('app.log_max_length'));
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
     * @param string $tag
     * @return Logger
     */
    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
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
}
