<?php

namespace Mirage\Events;

use Mirage\Constants\Services;
use Mirage\Libs\L;
use Phalcon\Events\Event;

/**
 * Class Auth
 * @package Mirage
 */

class DbProfiler extends \Mirage\App\Event
{

    public function __construct()
    {
        $this->profiler = \Phalcon\Di::getDefault()->get(Services::PROFILE);
    }

    public function ping($connection)
    {
        try {
            $connection->fetchAll('SELECT 1');
        } catch (\Exception $e) {
            L::e('connection to db is lost. try to reconnect');
            $connection->connect();
        }
        return $connection;
    }
    public function beforeQuery(Event $event, $connection)
    {
        $connection = $this->ping($connection);
        $this->profiler->startProfile(
            $connection->getSQLStatement()
        );
    }

    public function afterQuery(Event $event, $connection)
    {
        $this->profiler->stopProfile();
    }

    public function getProfiler()
    {
        return $this->profiler;
    }

    public function action()
    {
        // TODO: Implement action() method.
    }
}
