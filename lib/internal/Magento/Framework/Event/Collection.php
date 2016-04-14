<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            $this->addEvent(new Event(['name' => $eventName]));
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
    public function dispatch($eventName, array $data = [])
    {
        $event = $this->getEventByName($eventName);
        $event->addData($data)->dispatch();
        $this->getGlobalObservers()->dispatch($event);
        return $this;
    }
}
