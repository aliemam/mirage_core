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

namespace Mirage\App;

use Mirage\Http\Response;

class Controller extends \Phalcon\Mvc\Controller
{

    /**
     * Predefined action to see if Service is up.
     *
     * @return Response
     */
    public function checkMicroService(): Response
    {
        return Response::create(["test" => "ok"]);
    }

    /**
     * destruct everything.
     *
     * @return void
     */
    public function __destruct(): void
    {
        foreach ($this as &$value) {
            $value = null;
        }
    }
}
