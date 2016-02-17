<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ResponseInterface;

/**
 * Main application class for invoking applications
 *
 * Expects the user will provide a configured ServiceManager, configured with
 * the following services:
 *
 * - EventManager
 * - ModuleManager
 * - Request
 * - Response
 * - RouteListener
 * - Router
 * - DispatchListener
 * - ViewManager
 *
 * The most common workflow is:
 * <code>
 * $services = new Zend\ServiceManager\ServiceManager($servicesConfig);
 * $app      = new Application($appConfig, $services);
 * $app->bootstrap();
 * $response = $app->run();
 * $response->send();
 * </code>
 *
 * bootstrap() opts in to the default route, dispatch, and view listeners,
 * sets up the MvcEvent, and triggers the bootstrap event. This can be omitted
 * if you wish to setup your own listeners and/or workflow; alternately, you
 * can simply extend the class to override such behavior.
 */
class Application implements
    ApplicationInterface,
    EventManagerAwareInterface
{
    const ERROR_CONTROLLER_CANNOT_DISPATCH = 'error-controller-cannot-dispatch';
    const ERROR_CONTROLLER_NOT_FOUND       = 'error-controller-not-found';
    const ERROR_CONTROLLER_INVALID         = 'error-controller-invalid';
    const ERROR_EXCEPTION                  = 'error-exception';
    const ERROR_ROUTER_NO_MATCH            = 'error-router-no-match';

    /**
     * @var array
     */
    protected $configuration = null;

    /**
     * Default application event listeners
     *
     * @var array
     */
    protected $defaultListeners = array(
        'RouteListener',
        'DispatchListener',
        'HttpMethodListener',
        'ViewManager',
        'SendResponseListener',
    );

    /**
     * MVC event token
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var \Zend\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ServiceManager
     */
    protected $serviceManager = null;

    /**
     * Constructor
     *
     * @param mixed $configuration
     * @param ServiceManager $serviceManager
     */
    public function __construct($configuration, ServiceManager $serviceManager)
    {
        $this->configuration  = $configuration;
        $this->serviceManager = $serviceManager;

        $this->setEventManager($serviceManager->get('EventManager'));

        $this->request        = $serviceManager->get('Request');
        $this->response       = $serviceManager->get('Response');
    }

    /**
     * Retrieve the application configuration
     *
     * @return array|object
     */
    public function getConfig()
    {
        return $this->serviceManager->get('Config');
    }

    /**
     * Bootstrap the application
     *
     * Defines and binds the MvcEvent, and passes it the request, response, and
     * router. Attaches the ViewManager as a listener. Triggers the bootstrap
     * event.
     *
     * @param array $listeners List of listeners to attach.
     * @return Application
     */
    public function bootstrap(array $listeners = array())
    {
        $serviceManager = $this->serviceManager;
        $events         = $this->events;

        $listeners = array_unique(array_merge($this->defaultListeners, $listeners));

        foreach ($listeners as $listener) {
            $events->attach($serviceManager->get($listener));
        }

        // Setup MVC Event
        $this->event = $event  = new MvcEvent();
        $event->setTarget($this);
        $event->setApplication($this)
              ->setRequest($this->request)
              ->setResponse($this->response)
              ->setRouter($serviceManager->get('Router'));

        // Trigger bootstrap events
        $events->trigger(MvcEvent::EVENT_BOOTSTRAP, $event);
        return $this;
    }

    /**
     * Retrieve the service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get the request object
     *
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the response object
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the MVC event instance
     *
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->event;
    }

    /**
     * Set the event manager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return Application
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $this->events = $eventManager;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Static method for quick and easy initialization of the Application.
     *
     * If you use this init() method, you cannot specify a service with the
     * name of 'ApplicationConfig' in your service manager config. This name is
     * reserved to hold the array from application.config.php.
     *
     * The following services can only be overridden from application.config.php:
     *
     * - ModuleManager
     * - SharedEventManager
     * - EventManager & Zend\EventManager\EventManagerInterface
     *
     * All other services are configured after module loading, thus can be
     * overridden by modules.
     *
     * @param array $configuration
     * @return Application
     */
    public static function init($configuration = array())
    {
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $serviceManager = new ServiceManager(new Service\ServiceManagerConfig($smConfig));
        $serviceManager->setService('ApplicationConfig', $configuration);
        $serviceManager->get('ModuleManager')->loadModules();

        $listenersFromAppConfig     = isset($configuration['listeners']) ? $configuration['listeners'] : array();
        $config                     = $serviceManager->get('Config');
        $listenersFromConfigService = isset($config['listeners']) ? $config['listeners'] : array();

        $listeners = array_unique(array_merge($listenersFromConfigService, $listenersFromAppConfig));

        return $serviceManager->get('Application')->bootstrap($listeners);
    }

    /**
     * Run the application
     *
     * @triggers route(MvcEvent)
     *           Routes the request, and sets the RouteMatch object in the event.
     * @triggers dispatch(MvcEvent)
     *           Dispatches a request, using the discovered RouteMatch and
     *           provided request.
     * @triggers dispatch.error(MvcEvent)
     *           On errors (controller not found, action not supported, etc.),
     *           populates the event with information about the error type,
     *           discovered controller, and controller class (if known).
     *           Typically, a handler should return a populated Response object
     *           that can be returned immediately.
     * @return self
     */
    public function run()
    {
        $events = $this->events;
        $event  = $this->event;

        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) use ($event) {
            if ($r instanceof ResponseInterface) {
                return true;
            }
            if ($event->getError()) {
                return true;
            }
            return false;
        };

        // Trigger route event
        $result = $events->trigger(MvcEvent::EVENT_ROUTE, $event, $shortCircuit);
        if ($result->stopped()) {
            $response = $result->last();
            if ($response instanceof ResponseInterface) {
                $event->setTarget($this);
                $event->setResponse($response);
                $events->trigger(MvcEvent::EVENT_FINISH, $event);
                $this->response = $response;
                return $this;
            }
        }

        if ($event->getError()) {
            return $this->completeRequest($event);
        }

        // Trigger dispatch event
        $result = $events->trigger(MvcEvent::EVENT_DISPATCH, $event, $shortCircuit);

        // Complete response
        $response = $result->last();
        if ($response instanceof ResponseInterface) {
            $event->setTarget($this);
            $event->setResponse($response);
            $events->trigger(MvcEvent::EVENT_FINISH, $event);
            $this->response = $response;
            return $this;
        }

        $response = $this->response;
        $event->setResponse($response);
        $this->completeRequest($event);

        return $this;
    }

    /**
     * @deprecated
     */
    public function send()
    {
    }

    /**
     * Complete the request
     *
     * Triggers "render" and "finish" events, and returns response from
     * event object.
     *
     * @param  MvcEvent $event
     * @return Application
     */
    protected function completeRequest(MvcEvent $event)
    {
        $events = $this->events;
        $event->setTarget($this);
        $events->trigger(MvcEvent::EVENT_RENDER, $event);
        $events->trigger(MvcEvent::EVENT_FINISH, $event);
        return $this;
    }
}
