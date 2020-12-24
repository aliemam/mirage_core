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

use ErrorException;
use Mirage\App\RoutesCollection;
use App\Constants\Err;
use App\Constants\Services;
use Mirage\Exceptions\HttpException;
use Mirage\Http\Request;
use Mirage\Libs\Config;
use Mirage\Libs\Helper;
use Mirage\Libs\L;
use Mirage\Libs\Route;
use Phalcon\Di;
use phpDocumentor\Reflection\Types\This;

/**
 * RestApp class
 */
class RestApp extends \Phalcon\Mvc\Micro
{
    private array $services = [];
    private array $collections = [];
    private array $events = [];

    public function __construct(\Phalcon\Di $di)
    {
        parent::__construct();

        // create container
        $this->setDi($di);
        \Phalcon\Di::setDefault($di);
    }

    /**
     * This function boots all frameworks default services and routes and events
     *
     * @return void
     */
    public function bootFrameworkDefaults(): void
    {
//        // add framework default services
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
            $manager = new \Phalcon\Events\Manager();
            return $manager;
        });

        $event_manager = Di::getDefault()->getShared(Services::EVENTS_MANAGER);
        $this->setEventsManager($event_manager);
    }

    /**
     * This function boots all frameworks default services and routes and events
     *
     * @return void
     * @throws ErrorException
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
                    $route_class = "\\App\\Routes\\$route";
                    if (class_exists($route_class)) {
                        $route_obj = new $route_class;
                        $this->addCollection($route_obj);
                    } else {
                        L::w("Class $route_class not exists");
                    }
                }
            }

            // add events
            if (is_readable(APP_DIR . '/events')) {
                $events = opendir(APP_DIR . '/events');
                while ($event = readdir($events)) {
                    if (strpos($event, '.php') === false) {
                        continue;
                    }
                    $event = str_replace('.php', '', $event);
                    $event_class = "\\App\\Events\\$event";
                    if (class_exists($event_class)) {
                        $event_obj = new $event_class;
                        $this->addEvent($event_obj);
                    } else {
                        L::w("Class $event_class not exists");
                    }
                }
            }
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
        $this->getDi()->setShared($name, $service);

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

    public static function getSrv(string $service_name)
    {
        return \Phalcon\Di::getDefault()->getShared($service_name);
    }

    /**
     * Mount new Collection
     *
     * @param RoutesCollection $collection
     * @return RestApp
     * @throws ErrorException
     */
    public function addCollection(RoutesCollection $collection): RestApp
    {
        $this->collections[$collection->getId()] = $collection;
        $collection->boot();
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

    public function addEvent(Event $event): RestApp
    {
//        $this->getDi()->get(Services::EVENTS_MANAGER)->enablePriorities(true);
//        $this->getDi()->get(Services::EVENTS_MANAGER)->attach($event_name, $even_action, count($this->events));
//        $event_name->setEventsManager($this->getDi()->get(Services::EVENTS_MANAGER));
//        $this->events[] = [$service_name, $action, $service];
        return $this;
    }


    public function getEvents(): array
    {
        return $this->events;
    }


    /**
     * Run ResFullApp
     *
     * @return void
     * @throws ErrorException
     */
    public function run(): void
    {
        L::i('Request START: ' . json_encode($this->request->getQuery()));
        L::i('Request HEADERS: ' . json_encode(Helper::getHeaders()));
        L::i('Request BODY: ' . json_encode($this->request->getRawBody(true)));
        L::i('Request POST: ' . json_encode($this->request->getPost()));
        
        if ($this->request->isOptions()) {
            $this->response->createOptionResponseHeaders();
            $this->response->setStatusCode(200, 'OK');
            $this->response->sendHeaders();

            return;
        };

        $this->before(function () {
            $route = Request::getRoute();
            foreach ($route->getMiddlewares() as $middleware){
                $middleware->check();
            }
        });

        $this->after(function () {
            $result = $this->getReturnedValue();
            $result->sendResponse();
        });

        $this->notFound(function () {
            throw new HttpException(
                Err::REQUEST_NOT_FOUND,
                'Route not Found: ' . $this->request->getURI()
            );
        });

        $this->handle($_SERVER['REQUEST_URI']);
    }
}
