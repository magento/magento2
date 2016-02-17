<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\EventManager;

use Zend\Stdlib\CallbackHandler;
use Zend\Stdlib\PriorityQueue;

/**
 * Event manager: notification system
 *
 * Use the EventManager when you want to create a per-instance notification
 * system for your objects.
 */
class GlobalEventManager
{
    /**
     * @var EventManagerInterface
     */
    protected static $events;

    /**
     * Set the event collection on which this will operate
     *
     * @param  null|EventManagerInterface $events
     * @return void
     */
    public static function setEventCollection(EventManagerInterface $events = null)
    {
        static::$events = $events;
    }

    /**
     * Get event collection on which this operates
     *
     * @return EventManagerInterface
     */
    public static function getEventCollection()
    {
        if (null === static::$events) {
            static::setEventCollection(new EventManager());
        }
        return static::$events;
    }

    /**
     * Trigger an event
     *
     * @param  string        $event
     * @param  object|string $context
     * @param  array|object  $argv
     * @param  null|callable $callback
     * @return ResponseCollection
     */
    public static function trigger($event, $context, $argv = array(), $callback = null)
    {
        return static::getEventCollection()->trigger($event, $context, $argv, $callback);
    }

    /**
     * Trigger listeners until return value of one causes a callback to evaluate
     * to true.
     *
     * @param  string $event
     * @param  string|object $context
     * @param  array|object $argv
     * @param  callable $callback
     * @return ResponseCollection
     * @deprecated Please use trigger()
     */
    public static function triggerUntil($event, $context, $argv, $callback)
    {
        trigger_error(
            'This method is deprecated and will be removed in the future. Please use trigger() instead.',
            E_USER_DEPRECATED
        );
        return static::trigger($event, $context, $argv, $callback);
    }

    /**
     * Attach a listener to an event
     *
     * @param  string $event
     * @param  callable $callback
     * @param  int $priority
     * @return CallbackHandler
     */
    public static function attach($event, $callback, $priority = 1)
    {
        return static::getEventCollection()->attach($event, $callback, $priority);
    }

    /**
     * Detach a callback from a listener
     *
     * @param  CallbackHandler $listener
     * @return bool
     */
    public static function detach(CallbackHandler $listener)
    {
        return static::getEventCollection()->detach($listener);
    }

    /**
     * Retrieve list of events this object manages
     *
     * @return array
     */
    public static function getEvents()
    {
        return static::getEventCollection()->getEvents();
    }

    /**
     * Retrieve all listeners for a given event
     *
     * @param  string $event
     * @return PriorityQueue|array
     */
    public static function getListeners($event)
    {
        return static::getEventCollection()->getListeners($event);
    }

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public static function clearListeners($event)
    {
        static::getEventCollection()->clearListeners($event);
    }
}
