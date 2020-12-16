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

use Phalcon\Security\Random as Random;

/**
 * Class Helper
 * @package Mirage\Libs
 */
class Helper
{
    /**
     * Generates hash key by array of strings.
     * @param mixed ...$params
     * @return string
     */
    public static function getUniqueId(...$params): string
    {
        return (string)crc32(str_replace(['{', '}', '[', ']'], ['', '', '', ''], implode(':', $params)));
    }

    /**
     * Check if the given ip is valid
     * @param $ip
     * @return bool
     */
    public static function validateIp(string $ip): bool
    {
        if (strtolower($ip) === 'unknown') {
            return false;
        }

        // generate ipv4 network address
        $ip = ip2long($ip);

        // if the ip is set and not equivalent to 255.255.255.255
        if ($ip !== false && $ip !== -1) {
            // make sure to get unsigned long representation of ip
            // due to discrepancies between 32 and 64 bit OSes and
            // signed numbers (ints default to signed in PHP)
            $ip = sprintf('%u', $ip);
            // do private network range checking
            if ($ip >= 0 && $ip <= 50331647) {
                return false;
            }
            if ($ip >= 167772160 && $ip <= 184549375) {
                return false;
            }
            if ($ip >= 2130706432 && $ip <= 2147483647) {
                return false;
            }
            if ($ip >= 2851995648 && $ip <= 2852061183) {
                return false;
            }
            if ($ip >= 2886729728 && $ip <= 2887778303) {
                return false;
            }
            if ($ip >= 3221225984 && $ip <= 3221226239) {
                return false;
            }
            if ($ip >= 3232235520 && $ip <= 3232301055) {
                return false;
            }
            if ($ip >= 4294967040) {
                return false;
            }
        }
        return true;
    }

    /**
     * Calculates the ip of request.
     * @return string
     */
    public static function getIP(): string
    {
        // check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validateIp($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    if (self::validateIp($ip)) {
                        return $ip;
                    }
                }
            } else {
                if (self::validateIp($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validateIp($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validateIp($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validateIp($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED']) && self::validateIp($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }

        // return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    /**
     * Returns all header in request.
     * @return array
     */
    public static function getHeaders(): array
    {
        $headers = [];
        foreach (getallheaders() as $k => $v) {
            $headers[strtolower($k)] = $v;
        }

        return $headers;
    }

    /**
     * Returns value of header by key.
     * @param $key
     * @return string|null
     */
    public static function getHeader(string $key): ?string
    {
        $headers = self::getHeaders();
        return ($headers[strtolower($key)] ?? null);
    }

    /**
     * Generates uuid random hash.
     * @return string
     */
    public static function getUUID(): string
    {
        $r = new Random();
        return $r->uuid();
    }

    /**
     * When somewhere in code function json_encode or json_decode was called,
     * you can check if everything went right or not by calling this function.
     * @param $str
     * @return string|null
     */
    public static function jsonErrorMsg(string $str): ?string
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return null;
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }

    /**
     * Returns number of cup cores.
     * @return int
     */
    public static function getCpuCores(): int
    {
        $numCpus = 1;
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $numCpus = count($matches[0]);
        } elseif ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
            $process = @popen('wmic cpu get NumberOfCores', 'rb');
            if (false !== $process) {
                $numCpus = intval(fgets($process));
                pclose($process);
            }
        } else {
            $process = @popen('sysctl -a', 'rb');
            if (false !== $process) {
                $output = stream_get_contents($process);
                preg_match('/hw.ncpu: (\d+)/', $output, $matches);
                if ($matches) {
                    $numCpus = intval($matches[1][0]);
                }
                pclose($process);
            }
        }

        return $numCpus;
    }

    /**
     * This function converts text in camel case format to under score format
     * @param $input
     * @return string
     * @example given helloWorld returns hello_world
     */
    public static function convertCamelCaseToUnderScore($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    /**
     * This function converts text in under score format to camel case format
     * @param $input
     * @return string
     * @example given hello_world returns helloWorld
     */
    public static function convertUnderScoreToCamelCase($input)
    {
        if (strpos($input, '_') === false) {
            return $input;
        }
        $res = '';
        $words = explode('_', $input);
        foreach ($words as $word) {
            $res .= ucfirst($word);
        }
        return lcfirst($res);
    }

    /**
     * This function redirect client to other pages.
     * NOTE: $terminate_request = true, works if this script was ran by fast-cgi engine
     *
     * @param string $url
     * @param boolean $terminate_request
     * @return void
     */
    public static function redirect(string $url, bool $terminate_request = true)
    {
        ignore_user_abort(true);
        set_time_limit(10);
        L::d("System Redirect to : $url");
        header('Location: ' . $url);
        header('Connection: close');
        echo "<meta http-equiv='refresh' content='0; url={$url}' />";
        echo "<script>window.location.href = '{$url}';</script>";
        if ($terminate_request) {
            L::d("Terminate request connection");
            exit(0);
        } else {
            fastcgi_finish_request();
            L::d("continue the script...");
        }
    }

    /**
     * Get value by passing keys in host environment variables.
     * if it was not exist $default_value was returned.
     *
     * @param string $key
     * @param string|null $default_value
     * @return mixed
     */
    public static function env(string $key, string $default_value = null)
    {
        $env_value = $_ENV[$key] ?? false;
        if ($env_value !== false) {
            if (isset($env_value[0])) {
                if ($env_value[0] == '[' && $env_value[strlen($env_value) - 1] == ']') {
                    $new_env_value = str_replace(['[', ']', "'"], '', $env_value);
                    $env_value = explode(',', $new_env_value);
                    return $env_value;
                }
            }

            if (strtolower($env_value) === "true" || strtolower($env_value) === "false") {
                return (strtolower($env_value) === "true");
            }

            return $env_value;
        }
        return $default_value;
    }

    /**
     * Converts given object to stdClass. remember this just returns public variables of the object.
     *
     * @param $object
     * @return \stdClass
     */
    public function castToStd($object){
        $variables = get_object_vars($object);
        $std = new \stdClass();
        foreach ($variables as $variable => $value) {
            $std->$variable = $value;
        }

        return $std;
    }
}
