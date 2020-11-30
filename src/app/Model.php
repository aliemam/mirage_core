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

use Mirage\Constants\Err;
use Mirage\Exceptions\HttpException;
use Mirage\Libs\L;
use Phalcon\Mvc\Model as PhalconModel;

class Model extends PhalconModel implements \JsonSerializable
{
    public int $created_at;
    public int $updated_at;
    private bool $force_terminated = false;
    private bool $call_after_fetch = false;

    /**
     * This method calls just one and initiate model.
     *
     * @return void
     */
    public function initialize(): void
    {
        Model::setup(
            [
                'disableAssignSetters' => true
            ]
        );
        $this->useDynamicUpdate(true);
    }

    /**
     * Before validating model on creating, this function sets created_at and update_at columns.
     *
     * @return void
     */
    public function beforeValidationOnCreate(): void
    {
        $this->created_at = $this->updated_at = time();
    }

    /**
     * Before validating model on updating, this function sets update_at column.
     *
     * @return void
     */
    public function beforeValidationOnUpdate(): void
    {
        $this->updated_at = time();
    }

    /**
     * Always calls afterFetch model event on saving
     *
     * @return void
     */
    public function afterSave(): void
    {
        if ($this->call_after_fetch) {
            $this->afterFetch();
        }
    }

    /**
     * This function handle all of saving job.
     *
     * @param boolean $force_terminate_on_error
     * @param boolean $calling_after_fetch
     * @return bool
     */
    public function saveModel($force_terminate_on_error = true, $calling_after_fetch = false): bool
    {
        $this->force_terminated = $force_terminate_on_error;
        $this->call_after_fetch = $calling_after_fetch;
        $this->getReadConnection()->query("SET NAMES UTF8");
        return $this->save();
    }

    /**
     * If by any reason model not saved, this function is triggered.
     *
     * @throws HttpException
     * @throws \ErrorException
     */
    public function notSaved(): void
    {
        $err = [];
        foreach ($this->getMessages() as $msg) {
            $err[] = $msg->getMessage();
        }
        $msg_on_error = join(', ', $err);
        if ($this->force_terminated) {
            throw new HttpException(Err::DATABASE_SAVE, $msg_on_error);
        } else {
            L::e($msg_on_error);
        }
    }

    /**
     * This function helps to use IN keyword.
     *
     * @param $column_name
     * @param $values
     * @return array
     */
    public static function findIn($column_name, $values): array
    {
        if (!isset($values) || !isset($column_name)) {
            return [];
        }
        $object = get_called_class();
        $arr = [];
        for ($i = 0; $i < count($values); $i++) {
            $arr[] = "?$i";
        }
        return $object::find([
            "conditions" => "$column_name IN (" . implode(',', array_reverse($arr)) . ")",
            "bind" => $values
        ]);
    }
}
