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

use Phalcon\Logger as PLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Phalcon\Logger\Adapter\File as LoggerAdapter;

/**
 * Class Logger
 * @package Mirage\Libs
 */
class Logger implements LoggerInterface
{
    /** @var array of Logger Objects that store here as Singleton Object */
    private static array $loggers = [];

    /**
     * @var string each logger has name that can be identified by that name
     */
    private static string $logger_name;

    /** @var LoggerAdapter Phalcon Logger object that actually handles logging process */
    private LoggerAdapter $logger;

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
     * @param $logger_name
     * @throws \ErrorException
     */
    private function __construct($logger_name)
    {
        try {
            $this->logger =
                new LoggerAdapter(LOG_DIR . '/' . $logger_name . '_' . date('Y-m-d', time()), ['mode' => 'a']);
            switch (Config::get('app.env')) {
                case 'pro':
                    $this->logger->setLogLevel(PLogger::ERROR);
                    break;
                case 'dev':
                    $this->logger->setLogLevel(PLogger::DEBUG);
                    break;
            }
            $tag = $this->tag ?? 'no_tag';
            $ip = php_sapi_name() != "cli" ? 'cli_mode' : Helper::getIP();
            $route = $_SERVER['REQUEST_URI'] ?? 'not_http_request';
            $this->prefix = "[$tag][$ip][$route]";
        } catch (\Exception $e) {
            throw new \ErrorException('Cant create Logger: ' . $logger_name . ' :' . $e->getMessage());
        }
    }
    public function __destruct()
    {
        foreach ($this as &$value) {
            $value = null;
        }
    }

    /**
     * Here new logger will be created if there was no logger with that logger_name was created before.
     * @param string|null $logger_name
     * @throws \ErrorException
     */
    public static function create(string $logger_name = null): void
    {
        self::$logger_name = $logger_name ?? 'mirage';
        if (!isset(self::$loggers[$logger_name])) {
            self::$loggers[$logger_name] = new Logger($logger_name);
        }
    }

    /**
     * Get single instance of Logger Object
     * @param string|null $logger_name
     * @return LoggerAdapter
     * @throws \ErrorException
     */
    protected static function instance(string $logger_name = null): LoggerAdapter
    {
        if (!isset(self::$logger_name) || !isset(self::$loggers[$logger_name])) {
            self::create($logger_name);
        }
        return self::$loggers[$logger_name]->logger;
    }

    /**
     * This function just reduce length of message
     * @param $msg
     * @return string
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
     * @param $tag
     * @return string
     * @throws \ErrorException
     */
    public static function setTag($tag): string
    {
        if (!isset(self::$logger_name) || !isset(self::$loggers[self::$logger_name])) {
            throw new \ErrorException('You should first create Logger: ' . self::$logger_name);
        }
        self::$loggers[self::$logger_name]->tag = $tag;
    }

    /**
     * @return string
     * @throws \ErrorException
     */
    public static function getTag(): string
    {
        if (!isset(self::$logger_name) || !isset(self::$loggers[self::$logger_name])) {
            throw new \ErrorException('You should first create Logger: ' . self::$logger_name);
        }
        return self::$loggers[self::$logger_name]->tag;
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function emergency($msg, array $arr = []): void
    {
        $this->logger->emergency($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function alert($msg, array $arr = []): void
    {
        $this->logger->alert($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function critical($msg, array $arr = []): void
    {
        $this->logger->critical($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function error($msg, array $arr = []): void
    {
        $this->logger->error($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function warning($msg, array $arr = []): void
    {
        $this->logger->warning($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function notice($msg, array $arr = []): void
    {
        $this->logger->notice($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function info($msg, array $arr = []): void
    {
        $this->logger->info($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function debug($msg, array $arr = []): void
    {
        $this->logger->debug($this->shortMsg($this->prefix . $msg));
    }

    /**
     * @param string $msg
     * @param array $arr
     */
    public function log($level, $msg, array $arr = []): void
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                $this->emergency($msg, $arr);
                break;
            case LogLevel::ALERT:
                $this->alert($msg, $arr);
                break;
            case LogLevel::CRITICAL:
                $this->critical($msg, $arr);
                break;
            case LogLevel::ERROR:
                $this->error($msg, $arr);
                break;
            case LogLevel::WARNING:
                $this->warning($msg, $arr);
                break;
            case LogLevel::NOTICE:
                $this->notice($msg, $arr);
                break;
            case LogLevel::INFO:
                $this->info($msg, $arr);
                break;
            default:
                $this->debug($msg, $arr);
                break;
        }
    }
}
