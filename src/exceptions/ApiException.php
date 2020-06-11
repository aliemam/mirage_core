<?php
/**
 * Created by PhpStorm.
 * User: mirage
 * Date: 6/21/17
 * Time: 11:28 AM
 */

namespace Mirage\Exceptions;

use Mirage\Http\Response;

class ApiException extends \Exception
{

    private $response;
    
    public function __construct($dev_code, $dev_message)
    {
        parent::__construct($dev_message, (int)substr($dev_code, 1));
        $this->response = Response::create(null, $dev_message, $dev_code);
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getDevCode($code)
    {
        $start = ((int)strlen((string)$code)) * -1;
        return substr_replace('00000', $code, $start);
    }
}
