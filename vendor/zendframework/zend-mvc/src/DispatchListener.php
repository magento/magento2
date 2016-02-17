<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use ArrayObject;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Exception\InvalidControllerException;
use Zend\Stdlib\ArrayUtils;

/**
 * Default dispatch listener
 *
 * Pulls controllers from the service manager's "ControllerManager" service.
 *
 * If the controller cannot be found a "404" result is set up. Otherwise it
 * will continue to try to load the controller.
 *
 * If the controller is not dispatchable it sets up a "404" result. In case
 * of any other exceptions it trigger the "dispatch.error" event in an attempt
 * to return a 500 status.
 *
 * If the controller subscribes to InjectApplicationEventInterface, it injects
 * the current MvcEvent into the controller.
 *
 * It then calls the controller's "dispatch" method, passing it the request and
 * response. If an exception occurs, it triggers the "dispatch.error" event,
 * in an attempt to return a 500 status.
 *
 * The return value of dispatching the controller is placed into the result
 * property of the MvcEvent, and returned.
 */
class DispatchListener extends AbstractListenerAggregate
{
    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'));
        if (function_exists('zend_monitor_custom_event_ex')) {
            $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'reportMonitorEvent'));
        }
    }

    /**
     * Listen to the "dispatch" event
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch       = $e->getRouteMatch();
        $controllerName   = $routeMatch->getParam('controller', 'not-found');
        $application      = $e->getApplication();
        $events           = $application->getEventManager();
        $controllerLoader = $application->getServiceManager()->get('ControllerManager');

        if (!$controllerLoader->has($controllerName)) {
            $return = $this->marshalControllerNotFoundEvent($application::ERROR_CONTROLLER_NOT_FOUND, $controllerName, $e, $application);
            return $this->complete($return, $e);
        }

        try {
            $controller = $controllerLoader->get($controllerName);
        } catch (InvalidControllerException $exception) {
            $return = $this->marshalControllerNotFoundEvent($application::ERROR_CONTROLLER_INVALID, $controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        } catch (\Exception $exception) {
            $return = $this->marshalBadControllerEvent($controllerName, $e, $application, $exception);
            return $this->complete($return, $e);
        }

        $request  = $e->getRequest();
        $response = $application->getResponse();

        if ($controller instanceof InjectApplicationEventInterface) {
            $controller->setEvent($e);
        }

        try {
            $return = $controller->dispatch($request, $response);
        } catch (\Exception $ex) {
            $e->setError($application::ERROR_EXCEPTION)
                  ->setController($controllerName)
                  ->setControllerClass(get_class($controller))
                  ->setParam('exception', $ex);
            $results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $e);
            $return = $results->last();
            if (! $return) {
                $return = $e->getResult();
            }
        }

        return $this->complete($return, $e);
    }

    /**
     * @param MvcEvent $e
     */
    public function reportMonitorEvent(MvcEvent $e)
    {
        $error     = $e->getError();
        $exception = $e->getParam('exception');
        if ($exception instanceof \Exception) {
            zend_monitor_custom_event_ex($error, $exception->getMessage(), 'Zend Framework Exception', array('code' => $exception->getCode(), 'trace' => $exception->getTraceAsString()));
        }
    }

    /**
     * Complete the dispatch
     *
     * @param  mixed $return
     * @param  MvcEvent $event
     * @return mixed
     */
    protected function complete($return, MvcEvent $event)
    {
        if (!is_object($return)) {
            if (ArrayUtils::hasStringKeys($return)) {
                $return = new ArrayObject($return, ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $event->setResult($return);
        return $return;
    }

    /**
     * Marshal a controller not found exception event
     *
     * @param  string $type
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshalControllerNotFoundEvent(
        $type,
        $controllerName,
        MvcEvent $event,
        Application $application,
        \Exception $exception = null
    ) {
        $event->setError($type)
              ->setController($controllerName)
              ->setControllerClass('invalid controller class or alias: ' . $controllerName);
        if ($exception !== null) {
            $event->setParam('exception', $exception);
        }

        $events  = $application->getEventManager();
        $results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        $return  = $results->last();
        if (! $return) {
            $return = $event->getResult();
        }
        return $return;
    }

    /**
     * Marshal a controller not found exception event
     *
     * @deprecated Use marshalControllerNotFoundEvent() instead
     * @param  string $type
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshallControllerNotFoundEvent(
        $type,
        $controllerName,
        MvcEvent $event,
        Application $application,
        \Exception $exception = null
    ) {
        trigger_error(sprintf(
            '%s is deprecated; please use %s::marshalControllerNotFoundEvent instead',
            __METHOD__,
            __CLASS__
        ), E_USER_DEPRECATED);

        return $this->marshalControllerNotFoundEvent($type, $controllerName, $event, $application, $exception);
    }

    /**
     * Marshal a bad controller exception event
     *
     * @param  string $controllerName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshalBadControllerEvent(
        $controllerName,
        MvcEvent $event,
        Application $application,
        \Exception $exception
    ) {
        $event->setError($application::ERROR_EXCEPTION)
              ->setController($controllerName)
              ->setParam('exception', $exception);

        $events  = $application->getEventManager();
        $results = $events->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        $return  = $results->last();
        if (! $return) {
            return $event->getResult();
        }

        return $return;
    }
}
