<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\ResponseSender\ConsoleResponseSender;
use Zend\Mvc\ResponseSender\HttpResponseSender;
use Zend\Mvc\ResponseSender\PhpEnvironmentResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use Zend\Mvc\ResponseSender\SimpleStreamResponseSender;
use Zend\Stdlib\ResponseInterface as Response;

class SendResponseListener extends AbstractListenerAggregate implements
    EventManagerAwareInterface
{
    /**
     * @var SendResponseEvent
     */
    protected $event;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return SendResponseListener
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));
        $this->eventManager = $eventManager;
        $this->attachDefaultListeners();
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
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, array($this, 'sendResponse'), -10000);
    }

    /**
     * Send the response
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function sendResponse(MvcEvent $e)
    {
        $response = $e->getResponse();
        if (!$response instanceof Response) {
            return; // there is no response to send
        }
        $event = $this->getEvent();
        $event->setResponse($response);
        $event->setTarget($this);
        $this->getEventManager()->trigger($event);
    }

    /**
     * Get the send response event
     *
     * @return SendResponseEvent
     */
    public function getEvent()
    {
        if (!$this->event instanceof SendResponseEvent) {
            $this->setEvent(new SendResponseEvent());
        }
        return $this->event;
    }

    /**
     * Set the send response event
     *
     * @param SendResponseEvent $e
     * @return SendResponseEvent
     */
    public function setEvent(SendResponseEvent $e)
    {
        $this->event = $e;
        return $this;
    }

    /**
     * Register the default event listeners
     *
     * The order in which the response sender are listed here, is by their usage:
     * PhpEnvironmentResponseSender has highest priority, because it's used most often.
     * ConsoleResponseSender and SimpleStreamResponseSender are not used that often, yo they have a lower priority.
     * You can attach your response sender before or after every default response sender implementation.
     * All default response sender implementation have negative priority.
     * You are able to attach listeners without giving a priority and your response sender would be first to try.
     *
     * @return SendResponseListener
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new PhpEnvironmentResponseSender(), -1000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new ConsoleResponseSender(), -2000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new SimpleStreamResponseSender(), -3000);
        $events->attach(SendResponseEvent::EVENT_SEND_RESPONSE, new HttpResponseSender(), -4000);
    }
}
