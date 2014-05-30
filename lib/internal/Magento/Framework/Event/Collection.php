<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Collection of events
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Event;

use Magento\Framework\Event;

class Collection
{
    /**
     * Array of events in the collection
     *
     * @var array
     */
    protected $events;

    /**
     * Global observers
     * 
     * For example regex observers will watch all events that 
     *
     * @var Observer\Collection
     */
    protected $globalObservers;

    /**
     * Initializes global observers collection
     *
     * @param array $events
     * @param Observer\Collection $observerCollection
     */
    public function __construct(array $events = [], Observer\Collection $observerCollection = null)
    {
        $this->events = $events;
        $this->globalObservers = !$observerCollection ? new Observer\Collection() : $observerCollection;
    }

    /**
     * Returns all registered events in collection
     *
     * @return array
     */
    public function getAllEvents()
    {
        return $this->events;
    }

    /**
     * Returns all registered global observers for the collection of events
     *
     * @return Observer\Collection
     */
    public function getGlobalObservers()
    {
        return $this->globalObservers;
    }

    /**
     * Returns event by its name
     *
     * If event doesn't exist creates new one and returns it
     * 
     * @param string $eventName
     * @return Event
     */
    public function getEventByName($eventName)
    {
        if (!isset($this->events[$eventName])) {
            $this->addEvent(new Event(array('name' => $eventName)));
        }
        return $this->events[$eventName];
    }

    /**
     * Register an event for this collection
     *
     * @param Event $event
     * @return $this
     */
    public function addEvent(Event $event)
    {
        $this->events[$event->getName()] = $event;
        return $this;
    }

    /**
     * Register an observer
     * 
     * If observer has event_name property it will be registered for this specific event.
     * If not it will be registered as global observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function addObserver(Observer $observer)
    {
        $eventName = $observer->getEventName();
        if ($eventName) {
            $this->getEventByName($eventName)->addObserver($observer);
        } else {
            $this->getGlobalObservers()->addObserver($observer);
        }
        return $this;
    }

    /**
     * Dispatch event name with optional data
     *
     * Will dispatch specific event and will try all global observers
     *
     * @param string $eventName
     * @param array $data
     * @return $this
     */
    public function dispatch($eventName, array $data = array())
    {
        $event = $this->getEventByName($eventName);
        $event->addData($data)->dispatch();
        $this->getGlobalObservers()->dispatch($event);
        return $this;
    }
}
