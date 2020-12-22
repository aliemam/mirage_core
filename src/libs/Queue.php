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
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

/**
 * Class Beanstalk.php
 * @package Mirage\Libs
 */
// TODO: SHOULD BE IMPLEMENT AND INTERFACE, FOR NOW I JUST SOMETHING WORKING.
class Queue
{

    /**
     * @var Queue.
     */
    private static Queue $queue;

    /**
     * @var array beanstalk config
     */
    private static array $beanstalk_config;

    /** @var Pheanstalk of Cache Objects that store here as Singleton Object */
    private Pheanstalk $beanstalk;

    /**
     * @var array names of tubes in queue.
     */
    private array $tubes;

    /**
     * Cache constructor.
     * @param array $beanstalk_config
     * @throws ErrorException
     */
    private function __construct(array $beanstalk_config)
    {
        try {
            $this->beanstalk = Pheanstalk::create(
                $beanstalk_config['connection']['host'],
                $beanstalk_config['connection']['port'] ?? 11300,
                $beanstalk_config['connection']['timeout'] ?? 10
            );
            $this->tubes = $beanstalk_config['tubes'] ?? [];
            // TODO: any initialization

        } catch (\Exception $e) {
            throw new ErrorException('Cant create Beanstalk Object: ' . $e->getMessage());
        }
    }

    /**
     * Get single instance of Cache Object
     * @return Queue
     * @throws ErrorException
     */
    public static function getInstance(): ?Queue
    {
        if (!isset(self::$queue)) {
            if (!isset(self::$beanstalk_config) ||
                !isset(self::$beanstalk_config['connection']) ||
                !isset(self::$beanstalk_config['connection']['host'])
            ) {
                return null;
            }
            self::$queue = new Queue(self::$beanstalk_config);
        }
        return self::$queue;
    }

    public function clearAllTubes(): void
    {
        if (!isset($this->beanstalk)) return;

        foreach (self::$queue->listTubes() as $tube) {
            $this->beanstalk->useTube($tube);
            while (null !== $job = $this->beanstalk->peekReady()) {
                $this->beanstalk->delete($job);
            }
            while (null !== $job = $this->beanstalk->peekBuried()) {
                $this->beanstalk->delete($job);
            }
            while (null !== $job = $this->beanstalk->peekDelayed()) {
                $this->beanstalk->delete($job);
            }
        }
    }

    /**
     * Adds $data in string form to the $tube_name
     *
     * @param string $tube_name
     * @param string $data
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return Job
     */
    public function addDataToTube(
        string $tube_name,
        string $data,
        int $priority = Pheanstalk::DEFAULT_PRIORITY,
        int $delay = Pheanstalk::DEFAULT_DELAY,
        int $ttr = Pheanstalk::DEFAULT_TTR
    ): Job
    {
        return $this->beanstalk->withUsedTube($tube_name, function (Pheanstalk $beanstalk) use ($data, $priority, $delay, $ttr) {
            return $beanstalk->put($data, $priority, $delay, $ttr);
        });
    }

    /**
     * This function boot all beanstalk configs in the startup
     */
    public static function boot(): void
    {
        if (defined('CONFIG_DIR') && file_exists(CONFIG_DIR . '/beanstalk.php')) {
            self::$beanstalk_config = require CONFIG_DIR . '/beanstalk.php';
        }
    }
}
