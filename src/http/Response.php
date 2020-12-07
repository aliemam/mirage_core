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

namespace Mirage\Http;

use Mirage\Constants\Ok;
use Mirage\Libs\Config;
use Mirage\Libs\L;
use Mirage\Libs\Translator;

/**
 * Class Response
 *
 * @package Mirage
 */
final class Response extends \Phalcon\Http\Response
{
    private bool $has_error = false;
    private string $dev_message;
    private string $dev_code;
    private int $http_code;
    private $output;

    /**
     * Create new Response Object
     *
     * @param mixed $result
     * @param string $dev_code
     * @param string $dev_message
     * @return Response
     */
    public static function create(
        $result = null,
        string $dev_code = Ok::SUCCESS,
        string $dev_message = 'everything is good'
    ): Response
    {
        $response = new self;
        $response->has_error = ($dev_code[0] === 'f');
        $tmp = explode('-', $dev_code);
        $response->dev_message = $dev_message;
        $response->dev_code = $tmp[0];
        $response->http_code = $tmp[1];
        $response->output = $result ?? new \stdClass();

        return $response;
    }

    /**
     * Set developer message
     *
     * @param string $s
     * @return void
     */
    public function setDevMessage(string $s): Response
    {
        $this->dev_message = $s;
        return $this;
    }

    /**
     * Set developer response code
     *
     * @param string $s
     * @return void
     */
    public function setDevCode(string $s): Response
    {
        $this->dev_code = $s;
        return $this;
    }

    /**
     * Set output of response
     *
     * @param string $s
     * @return void
     */
    public function setOutput(string $s): Response
    {
        $this->output = $s;
        return $this;
    }

    /**
     * Generate appropriate message according to given http status code
     *
     * @param integer $code
     * @return string
     */
    private function getHttpResponseDescription(int $code): string
    {
        $codes = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',

            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',

            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded',
            //extended
            510 => 'Null Response',
        );

        $result = (isset($codes[$code])) ?
            $codes[$code] :
            'Unknown Http Code';

        return $result;
    }

    /**
     * When a request was option we use this function to send headers.
     *Result:
     * @return void
     */
    public function createOptionResponseHeaders(): void
    {
        // setup http for sending to client
        $allow_origins = implode(',', Config::get('app.allow_origins'));
        $allow_methods = implode(',', Config::get('app.allow_methods'));
        $allow_headers = implode(',', Config::get('app.allow_headers'));

        $this->setHeader('Access-Control-Allow-Credentials', true);
        $this->setHeader('Access-Control-Allow-Origin', $allow_origins);
        $this->setHeader('Access-Control-Allow-Methods', $allow_methods);
        $this->setHeader('Access-Control-Allow-Headers', $allow_headers);
    }

    public function sendResponse(): void
    {
        // logging everythings
        if (Config::get('app.log_mode') == 'complete') {
            $out_put_log = json_encode($this->output);
        } else {
            $out_put_log = substr(json_encode($this->output), 0, 500);
        }

        $log = "[Request Response Info] $this->http_code  $this->dev_code  $this->dev_message";
        $log_response = "[Request Response] $out_put_log";
        if ($this->has_error) {
            L::e($log);
            L::e($log_response);
        } else {
            L::i($log);
            L::i($log_response);
        }

        // create response object
        $response = new \stdClass();
        $response->status = new \stdClass();
        $response->status->http_code = $this->http_code;
        $response->status->dev_code = $this->dev_code;
        $response->status->dev_message = (Config::get('app.env') == 'dev' ? $this->dev_message : '');
        $response->status->message = Translator::get($this->dev_code);
        $response->output = $this->output;

        // setup http for sending to client
        $this->createOptionResponseHeaders();
        $this->setStatusCode((int)$this->http_code, $this->getHttpResponseDescription($this->http_code));
        $this->setEtag(md5(serialize($response)));
        $this->setContent(json_encode($response));

        $this->send();
    }
}
