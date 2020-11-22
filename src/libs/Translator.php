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
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

/**
 * Class Translator
 * This class handles multi language app. all languages files are in LANG_DIR will be loaded here.
 * @package Mirage\Libs
 */
final class Translator
{

    /** @var Translator Singleton instance */
    private static ?Translator $instance = null;

    /** @var AdapterInterface Phalcon NativeArray class that actually handles translating */
    private AdapterInterface $t;

    /**
     * Translator constructor.
     * @throws ErrorException
     */
    private function __construct()
    {
        try {
            $language = Config::get('app.lang');
            $translation_file = LANG_DIR . '/' . $language . '.php';

            if (file_exists($translation_file)) {
                $messages = require_once $translation_file;
            } else {
                $messages = require_once LANG_DIR . '/' . Config::get('app.callback_lang') . '.php';
            }

            $interpolator = new InterpolatorFactory();
            $factory      = new TranslateFactory($interpolator);
            $this->t = $factory->newInstance('array', ['content' => $messages]);
        } catch (\Exception $e) {
            throw new ErrorException('Cant load Translator: ' . $e->getMessage());
        }
    }

    /**
     * Get Translator single object
     * @return Translator
     */
    private static function getInstance(): Translator
    {
        self::$instance ??= new Translator();
        return self::$instance;
    }

    /**
     * Returns translated string by given code.
     * @param string $code
     * @param array $param
     * @return string
     */
    public static function get(string $code, array $param = []): string
    {
        $instance = self::getInstance();
        $tmp = explode('-', $code);
        return $instance->t->_($tmp[0], $param);
    }
}
