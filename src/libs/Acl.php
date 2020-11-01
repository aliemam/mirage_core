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
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;

/**
 * Class Acl
 * This class handles all access control operation.
 * @package Mirage\Libs
 */
class Acl
{

    /** @var Acl $instance is singleton instance of this class. */
    private static ?Acl $instance = null;

    /** @var Memory $acl is an access control list stores in memory. */
    private Memory $acl;

    /**
     * Acl constructor.
     * This function is private because this is Singleton class.
     * @throws ErrorException
     */
    private function __construct()
    {
        try {
            $this->acl = new Memory();
            $this->acl->setDefaultAction(\Phalcon\Acl::DENY);
            $this->acl->setNoArgumentsDefaultAction(\Phalcon\Acl::DENY);
        } catch (\Exception $e) {
            throw new ErrorException('Cant load Acl: ' . $e->getMessage());
        }
    }

    /**
     * Get single instance of class Acl for class internal use.
     * @return Acl
     * @throws ErrorException
     */
    private static function getInstance(): Acl
    {
        self::$instance ??= new Acl();
        return self::$instance;
    }

    /**
     * Get saved acl object in file and restore it in memory.
     * @return Acl
     * @throws ErrorException
     */
    public static function get(): Acl
    {
        $instance = self::getInstance();
        $cache_id = Helper::getUniqueId('mirage', 'acl');
        $acl = Cache::get($cache_id);
        if (!isset($acl)) {
            if (is_file(STORAGE_DIR . '/acl.data')) {
                L::d('acl is loading');
                $instance->acl = unserialize(file_get_contents(STORAGE_DIR . '/acl.data'));
                return $instance;
            } else {
                return null;
            }
        }
        $instance->acl = unserialize($acl);
        return $instance;
    }

    /**
     * Save serialized acl object in cache.
     * @throws ErrorException
     */
    public static function save(): void
    {
        $instance = self::getInstance();
        $cache_id = Helper::getUniqueId('mirage', 'acl');
        Cache::add($cache_id, serialize($instance->acl));
        file_put_contents(
            STORAGE_DIR . '/acl.data',
            serialize($instance->acl)
        );
    }

    /**
     * Remove acl from cache
     * @throws \Exception
     */
    public static function remove(): void
    {
        $cache_id = Helper::getUniqueId('mirage', 'acl');
        Cache::remove($cache_id);
    }

    /**
     * This function creates new role and assigns new operations to that role.
     * If $role_name was not exist in memory, this function try to create.
     * That $role_name for given $resource_name and add all operations to that role.
     * After all, This function save new acl to memory and overwrite previous one.
     * @param string $role_name This is a name of role that is allowed to do operation.
     * @param string $resource_name This is a name of resource that role is assigned to.
     * @param array $operation_names These are operations which the role allowed to operate.
     * @throws ErrorException
     */
    public static function allow(string $role_name, string $resource_name, array $operation_names): void
    {
        $instance = self::getInstance();
        L::d("Allow role: $role_name in resource: $resource_name on operations: " . json_encode($operation_names));
        $instance->acl->addRole(new Role($role_name, ''));
        $instance->acl->addResource(new Resource($resource_name), $operation_names);
        foreach ($operation_names as $operation_name) {
            $instance->acl->allow($role_name, $resource_name, $operation_name);
        }
        self::save();
    }

    /**
     * This function checks if $role_name is allowed to do $operation_name.
     * @param string $role_name This is a name of role that is allowed to do operation.
     * @param string $resource_name This is a name of resource that role is assigned to.
     * @param string $operation_name This is operation that role allowed to operate.
     * @return mixed
     * @throws ErrorException
     */
    public static function isAllowed(string $role_name, string $resource_name, string $operation_name): void
    {
        $instance = self::getInstance();
        L::d("Checking role: $role_name in resource: $resource_name and operation: $operation_name");
        return $instance->acl->isAllowed($role_name, $resource_name, $operation_name);
    }
}
