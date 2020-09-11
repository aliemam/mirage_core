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

namespace Mirage;

use Mirage\App\RoutesCollection;
use Mirage\Constants\Services;
use Mirage\Libs\L;

/**
 * RestApp class
 */
class RestApp extends \Phalcon\Mvc\Micro
{
    public function __construct()
    {
        parent::__construct();

        // create container
        $this->setDi(new \Phalcon\Di\FactoryDefault());
    }

    /**
     * This function boots all frameworks default services and routes and events
     *
     * @return void
     */
    public function bootFrameworkDefaults(): void
    {
        // add framework default services
        $this->addService(Services::REQUEST, function () {
            return new \Mirage\Http\Request();
        });
        $this->addService(Services::RESPONSE, function () {
            return new \Mirage\Http\Response();
        });
        $this->addService(Services::RANDOM, function () {
            return new \Phalcon\Security\Random();
        });
        $this->addService(Services::SECURITY, function () {
            return new \Phalcon\Security();
        });
        $this->addService(Services::TRANSACTION, function () {
            return new \Phalcon\Mvc\Model\Transaction\Manager();
        });
        $this->addService(Services::EVENTS_MANAGER, function () {
            $manager =  new \Phalcon\Events\Manager();
            // TODO:: here we should attach some default events
            return $manager;
        });

        // add framework default route
        $this->addCollection(RoutesCollection::healthCheckCollection());
    }

    /**
     * This function boots all frameworks default services and routes and events
     *
     * @return void
     */
    public function bootAppDefaults(): void
    {
        // add
        if (defined('APP_DIR')) {
            // add services
            $services = require_once APP_DIR . '/services.php';
            foreach ($services as $name => $service) {
                $this->addService($name, $service);
            }

            // add collection
            if (is_readable(APP_DIR . '/routes')) {
                $routes = opendir(APP_DIR . '/routes');
                while ($route = readdir($routes)) {
                    if (strpos($route, '.php') === false) {
                        continue;
                    }
                    $route = str_replace('.php', '', $route);
                    $route_class = "\App\Routes\$route";
                    $this->addCollection(new $route_class);
                }
            }

            // add events
            // if (is_readable(APP_DIR . '/events')) {
            //     $events = opendir($path);
            //     while ($event = readdir($events)) {
            //         if (strpos($event, '.php') === false) {
            //             continue;
            //         }
            //         $event = str_replace('.php', '', $event);
            //         $event_class = "\App\Event\$route";
            //         $this->addEvent($event_class);
            //     }
            // }
        }
    }

    /**
     * Add Service
     *
     * @param string $name
     * @param callable $service
     * @return RestApp
     */
    public function addService(string $name, callable $service): RestApp
    {
        $this->services[$name] = $service;
        $this->getDi()->setShared($service::Name, $service);

        return $this;
    }

    /**
     * Get All Services
     *
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Mount new Collection
     *
     * @param RoutesCollection $collection
     * @return RestApp
     */
    public function addCollection(RoutesCollection $collection): RestApp
    {
        $this->collections[$collection->id()] = $collection;
        $collection::boot();
        $this->mount($collection);
        return $this;
    }

    /**
     * Gets all RoutesCollections
     *
     * @return array
     */
    public function getCollections(): array
    {
        return $this->collections;
    }

    // public function addEvent(string $service_name, string $action, $service): RestApp
    // {
    //     $this->getDi()->get(Services::EVENTS_MANAGER)->enablePriorities(true);
    //         $this->getDi()->get(Services::EVENTS_MANAGER)->attach($event_name, $even_action, count($this->events));
    //         $event_name->setEventsManager($this->getDi()->get(Services::EVENTS_MANAGER));
    //     $this->events[] = [$service_name, $action, $service];
    //     return $this;
    // }


    // public function getEvents(): array
    // {
    //     return $this->events;
    // }


    /**
     * Run ResFullApp
     *
     * @return void
     */
    public function run(): void
    {
        L::d('[Request STARTS] ' . $_SERVER['REQUEST_URI']);
        L::d('[Request HEADERS] ' . json_encode(\Mirage\Libs\Helper::getHeaders()));

        if ($this->request->isOptions()) {
            $this->response->createOptionResponseHeaders();
            $this->response->sendHeaders();

            return true;
        };

        $this->before(function () {
            return true;
        });

        $this->after(function () {
            $this->getReturnedValue()->sendResponse();

            // if (\Mirage\Libs\Config::get('app.env') === 'dev') {
            //     $profiles = $this->getDi()->getShared(Services::PROFILE)->getProfiles();
            //     foreach ($profiles as $profile) {
            //         echo 'SQL Statement: ', $profile->getSQLStatement(), '\n';
            //         echo 'Start Time: ', $profile->getInitialTime(), '\n';
            //         echo 'Final Time: ', $profile->getFinalTime(), '\n';
            //         echo 'Total Elapsed Time: ', $profile->getTotalElapsedSeconds(), '\n';
            //     }
            // }
        });

        $this->notFound(function () {
            throw new HttpException(
                \Mirage\Constants\Err::REQUEST_NOT_FOUND,
                'Route not Found: ' . $this->request->getURI()
            );
        });

        $this->handle();
    }
}
