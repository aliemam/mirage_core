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

    /**
     * @var Response
     */
    private Response $response;

    /**
     * ApiException constructor.
     * @param string $dev_code
     * @param string $dev_message
     */
    public function __construct(string $dev_code, string $dev_message)
    {
        parent::__construct($dev_message, (int)substr($dev_code, 1));
        $this->response = Response::create(null, $dev_code, $dev_message);
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param string $code
     * @return string
     */
    public function getDevCode(string $code): string
    {
        $start = ((int)strlen((string)$code)) * -1;
        return substr_replace('00000', $code, $start);
    }
}
