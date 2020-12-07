<?php
///*
//  +------------------------------------------------------------------------+
//  | Mirage Framework                                                       |
//  +------------------------------------------------------------------------+
//  | Copyright (c) 2018-2020                                                |
//  +------------------------------------------------------------------------+
//  | This source file is subject to the New BSD License that is bundled     |
//  | with this package in the file LICENSE.txt.                             |
//  |                                                                        |
//  | If you did not receive a copy of the license and are unable to         |
//  | obtain it through the world-wide-web, please send an email             |
//  | to help@aemirage.com so we can send you a copy immediately.            |
//  +------------------------------------------------------------------------+
//  | Authors: Ali Emamhadi <aliemamhadi@aemirage.com>                       |
//  +------------------------------------------------------------------------+
//*/
//
///**
// * This is part of Mirage Micro Framework
// *
// * @author Ali Emamhadi <aliemamhadi@gmail.com>
// */
//
//namespace Mirage\Middleware;
//
//use Mirage\Constants\Err;
//use Mirage\Exceptions\HttpException;
//use Mirage\Libs\Auth;
//use Mirage\Libs\L;
//use Mirage\App\App;
//use Phalcon\Events\Event;
//
///**
// * Class Acl Middleware.
// * This class is builtin middleware that developers can use in app.
// * Note that its works with jwt token created by Auth class. if this token was not exists, this class it useless.
// * Note that this class checks parameter 'rn' in payload of jwt token
// * with is registered in Acl class in Libraries and takes a full route path az operation.
// * @package Mirage
// */
//
//class AclMiddleware extends Middleware
//{
//
//    /** @var string overwriting name of middleware */
//    const NAME = 'acl';
//
//    /** @var string jwt token provided by app */
//    protected string $token;
//
//    /**
//     * set jwt token
//     * @param string $token
//     */
//    public function setToken(string $token)
//    {
//        $this->token = $token;
//    }
//
//    /**
//     * Here we overwrite check method to implement our logic.
//     * This function will be called before executing route action.
//     * @param Event $event
//     * @param App $app
//     * @return bool
//     * @throws HttpException
//     * @throws \ErrorException
//     */
//    public function check(Event $event, App $app)
//    {
//        if (!isset($this->token)) {
//            L::w('Token is not set for this middleware so try to get token from Auth class');
//            $this->setToken(Auth::getPayload());
//            if (!isset($this->token)) {
//                throw new HttpException(Err::ACL_ACCESS_DENIED, 'cant find token so checking acl is useless');
//            }
//        }
//
//        if (!isset($this->token->rn)) {
//            throw new HttpException(
//                Err::ACL_CANT_FIND_ROLE_NAME,
//                'cant find role name in token so checking acl is useless'
//            );
//        }
//
//        $operation = $app->router->getMatchedRoute()->getName() .
//            '/' . $this->route_info['group'] . '/' . $this->route_info['code'] . '/' . $this->route_info['action'];
//        $can = \Mirage\Libs\Acl::can(
//            $this->token->rn,
//            $app->router->getMatchedRoute()->getName(),
//            $operation
//        );
//
//        if (!$can) {
//            throw new HttpException(Err::ACL_ACCESS_DENIED, 'user has not access to this api');
//        }
//
//        L::d('user has access');
//        return true;
//    }
//}
