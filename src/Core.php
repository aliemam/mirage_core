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

declare(strict_types=1);

namespace Mirage;

use ErrorException;
use Exception;
use App\Constants\Services;

/**
 * Undocumented class
 */
class Core
{
    public const UNI_CHANNEL = 'universal_channel';

    private static \Phalcon\Di $di;
    private static RestApp $rest_full_app;

    private function __construct()
    {
    }

    /**
     * Basic error handler function in framework
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     * @throws Exception
     */
    public static function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
    {
        $log = "";
        switch ($errno) {
            case E_PARSE:
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                $log = " [ERROR][$errno] $errstr. Fatal error on line $errline in file $errfile, PHP "
                    . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL . "Aborting..." . PHP_EOL;
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case E_RECOVERABLE_ERROR:
                $log = " [WARNING][$errno] $errstr. Warning on line $errline in file $errfile, PHP "
                    . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $log = " [NOTICE][$errno] $errstr. Notice on line $errline in file $errfile, PHP "
                    . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
                break;

            case E_STRICT:
                $log = " [STRICT][$errno] $errstr. Strict on line $errline in file $errfile, PHP "
                    . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
                break;

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $log = " [DEPRECATED][$errno] $errstr. Deprecated on line $errline in file $errfile, PHP "
                    . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
                break;

            default:
                $log = " Unknown error type: [$errno] $errstr\n";
                break;
        }
        throw new Exception($log);
    }

    /**
     * Basic shut down function in framework.
     *
     * @return void
     * @throws Exception
     */
    public static function shutdownHandler(): void
    {
        $error = error_get_last();
        if (isset($error)) {
            Core::errorHandler(
                (int)$error['type'],
                (string)$error['message'],
                (string)$error['file'],
                (int)$error['line']
            );
        }
    }

    /**
     * This functions boots handlers.
     *
     * @return void
     */
    private static function bootMirageFrameworkErrorHandlers(): void
    {
        ini_set('display_errors', 'off');
        error_reporting(E_ALL);
//        set_error_handler('\Mirage\Core::errorHandler');
//        register_shutdown_function('\Mirage\Core::shutdownHandler');
        define('ERR_HANDLER_LOADED', true);
    }

    /**
     * Define All environment variables for basic app using this framework.
     *
     * @return void
     * @throws ErrorException
     */
    private static function defineMirageAppEnvironmentVariables(): void
    {
        /**
         * Base App Paths
         */
        if (!defined('MIRAGE_APP_DIR')) {
            if (!is_readable(__DIR__ . '/../../../..')) {
                throw new ErrorException('[ERROR][100] Unable to define MIRAGE_APP_DIR');
            }
            define('MIRAGE_APP_DIR', __DIR__ . '/../../../..');
        }

        /**
         * App api or index Path
         */
        if (!defined('API_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/api')) {
                throw new ErrorException('[ERROR][100] Unable to define API_DIR');
            }
            define('API_DIR', MIRAGE_APP_DIR . '/api');
        }

        /**
         * App main folders
         */
        if (!defined('APP_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/app')) {
                throw new ErrorException('[ERROR][100] Unable to define APP_DIR');
            }
            define('APP_DIR', MIRAGE_APP_DIR . '/app');
        }

        /**
         * Bootstrap Path
         */
        if (!defined('BOOTSTRAP_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/bootstrap')) {
                throw new ErrorException('[ERROR][100] Unable to define BOOTSTRAP_DIR');
            }
            define('BOOTSTRAP_DIR', MIRAGE_APP_DIR . '/bootstrap');
        }
        if (!defined('CONFIG_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/config')) {
                throw new ErrorException('[ERROR][100] Unable to define CONFIG_DIR');
            }
            define('CONFIG_DIR', MIRAGE_APP_DIR . '/config');
        }
        if (!defined('DATABASE_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/database')) {
                throw new ErrorException('[ERROR][100] Unable to define DATABASE_DIR');
            }
            define('DATABASE_DIR', MIRAGE_APP_DIR . '/database');
        }
        if (!defined('LANG_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/lang')) {
                throw new ErrorException('[ERROR][100] Unable to define LANG_DIR');
            }
            define('LANG_DIR', MIRAGE_APP_DIR . '/lang');
        }
        if (!defined('LOG_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/log')) {
                throw new ErrorException('[ERROR][100] Unable to define LOG_DIR');
            }
            define('LOG_DIR', MIRAGE_APP_DIR . '/log');
        }

        /**
         * Base App Storage Paths
         */
        if (!defined('STORAGE_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/storage')) {
                throw new ErrorException('[ERROR][100] Unable to define STORAGE_DIR');
            }
            define('STORAGE_DIR', MIRAGE_APP_DIR . '/storage');
        }
        if (!defined('AUDIO_DIR')) {
            if (!is_readable(STORAGE_DIR . '/audio')) {
                throw new ErrorException('[ERROR][100] Unable to define AUDIO_DIR');
            }
            define('AUDIO_DIR', STORAGE_DIR . '/audio');
        }
        if (!defined('CACHE_DIR')) {
            if (!is_readable(STORAGE_DIR . '/cache')) {
                throw new ErrorException('[ERROR][100] Unable to define CACHE_DIR');
            }
            define('CACHE_DIR', STORAGE_DIR . '/cache');
        }
        if (!defined('EXECUTABLE_DIR')) {
            if (!is_readable(STORAGE_DIR . '/executable')) {
                throw new ErrorException('[ERROR][100] Unable to define EXECUTABLE_DIR');
            }
            define('EXECUTABLE_DIR', STORAGE_DIR . '/executable');
        }
        if (!defined('FILE_DIR')) {
            if (!is_readable(STORAGE_DIR . '/file')) {
                throw new ErrorException('[ERROR][100] Unable to define FILE_DIR');
            }
            define('FILE_DIR', STORAGE_DIR . '/file');
        }
        if (!defined('IMAGE_DIR')) {
            if (!is_readable(STORAGE_DIR . '/image')) {
                throw new ErrorException('[ERROR][100] Unable to define IMAGE_DIR');
            }
            define('IMAGE_DIR', STORAGE_DIR . '/image');
        }
        if (!defined('MIME_DIR')) {
            if (!is_readable(STORAGE_DIR . '/mime')) {
                throw new ErrorException('[ERROR][100] Unable to define MIME_DIR');
            }
            define('MIME_DIR', STORAGE_DIR . '/mime');
        }
        if (!defined('TEXT_DIR')) {
            if (!is_readable(STORAGE_DIR . '/text')) {
                throw new ErrorException('[ERROR][100] Unable to define TEXT_DIR');
            }
            define('TEXT_DIR', STORAGE_DIR . '/text');
        }
        if (!defined('VIDEO_DIR')) {
            if (!is_readable(STORAGE_DIR . '/video')) {
                throw new ErrorException('[ERROR][100] Unable to define VIDEO_DIR');
            }
            define('VIDEO_DIR', STORAGE_DIR . '/video');
        }

        /**
         * Base App Vendor Path
         */
        if (!defined('VENDOR_DIR')) {
            if (!is_readable(MIRAGE_APP_DIR . '/vendor')) {
                throw new ErrorException('[ERROR][100] Unable to define VENDOR_DIR');
            }
            define('VENDOR_DIR', MIRAGE_APP_DIR . '/vendor');
        }
        if (!defined('MIRAGE_DIR')) {
            if (!is_readable(VENDOR_DIR . '/aliemam/mirage_core/src')) {
                throw new ErrorException('[ERROR][100] Unable to define MIRAGE_DIR');
            }
            define('MIRAGE_DIR', VENDOR_DIR . '/aliemam/mirage_core/src');
        }

        define('ENV_VAR_LOADED', true);
    }

    /**
     * Register all namespaces, directories and files in both basic app and mirage itself.
     *
     * @return void
     * @throws ErrorException
     */
    private static function registerMirageNamespace(): void
    {
        // require vendor autoload
        require_once VENDOR_DIR . '/autoload.php';

        // require app autoload
        if (!is_readable(BOOTSTRAP_DIR . '/autoload.php')) {
            throw new ErrorException('[ERROR][100] Unable to read autoload.php from ' .
                BOOTSTRAP_DIR . '/autoload.php');
        }
        $app_auto_loader = require BOOTSTRAP_DIR . '/autoload.php';
        $framework_auto_loader = [
            'Namespaces' => [
                // mirage namespaces
                'Mirage' => MIRAGE_DIR,
                'Mirage\App' => MIRAGE_DIR . '/app/',
                'Mirage\Console' => MIRAGE_DIR . '/console/',
                'Mirage\Events' => MIRAGE_DIR . '/events/',
                'Mirage\Exceptions' => MIRAGE_DIR . '/exceptions/',
                'Mirage\Http' => MIRAGE_DIR . '/http/',
                'Mirage\Libs' => MIRAGE_DIR . '/libs/',
                'Mirage\Interfaces' => MIRAGE_DIR . '/interfaces/',
                'Mirage\Middleware' => MIRAGE_DIR . '/middleware/'
            ],
            'Dirs' => [],
            'Files' => []
        ];

        $namespaces = array_merge($framework_auto_loader['Namespaces'], $app_auto_loader['Namespaces'] ?? []);
        $dirs = array_merge($framework_auto_loader['Dirs'], $app_auto_loader['Dirs'] ?? []);
        $files = array_merge($framework_auto_loader['Files'], $app_auto_loader['Files'] ?? []);

        $loader = new \Phalcon\Loader();
        $loader->registerNamespaces($namespaces);
        $loader->registerDirs($dirs);
        foreach ($files as $file) {
            require_once $file;
        }

        $loader->register();

        define('NAMESPACES_LOADED', true);
    }

    /**
     * Mirage booting function
     * Brings up all needed components and register all needed namespaces.
     *
     * @return void
     * @throws ErrorException
     */
    public static function boot(): void
    {
        if(!extension_loaded('phalcon')) {
            throw new ErrorException('[ERROR][100] Phalcon can not be found!!!');
        }
        self::bootMirageFrameworkErrorHandlers();
        self::defineMirageAppEnvironmentVariables();
        self::registerMirageNamespace();
    }

    /**
     * Getting RestApp Instance
     *
     * @return RestApp
     */
    public static function getRestApp(): RestApp
    {
        if (!isset(self::$di)) {
            self::$di = new  \Phalcon\Di\FactoryDefault();
            \Phalcon\Di::setDefault(self::$di);
            \Phalcon\Di::getDefault()->setShared(Services::MICRO, function () {
                return new RestApp(\Phalcon\Di::getDefault());
            });
        }
        return \Phalcon\Di::getDefault()->getShared(Services::MICRO);
    }
}
