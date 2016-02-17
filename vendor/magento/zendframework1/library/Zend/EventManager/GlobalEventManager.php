<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Stdlib/CallbackHandler.php';
#require_once 'Zend/Stdlib/PriorityQueue.php';

/**
 * Event manager: notification system
 *
 * Use the EventManager when you want to create a per-instance notification
 * system for your objects.
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_GlobalEventManager
{
    /**
     * @var Zend_EventManager_EventCollection
     */
    protected static $events;

    /**
     * Set the event collection on which this will operate
     *
     * @param  null|Zend_EventManager_EventCollection $events
     * @return void
     */
    public static function setEventCollection(Zend_EventManager_EventCollection $events = null)
    {
        self::$events = $events;
    }

    /**
     * Get event collection on which this operates
     *
     * @return Zend_EventManager_EventCollection
     */
    public static function getEventCollection()
    {
        if (null === self::$events) {
            self::setEventCollection(new Zend_EventManager_EventManager());
        }
        return self::$events;
    }

    /**
     * Trigger an event
     *
     * @param  string $event
     * @param  object|string $context
     * @param  array|object $argv
     * @return Zend_EventManager_ResponseCollection
     */
    public static function trigger($event, $context, $argv = array())
    {
        return self::getEventCollection()->trigger($event, $context, $argv);
    }

    /**
     * Trigger listeenrs until return value of one causes a callback to evaluate
     * to true.
     *
     * @param  string $event
     * @param  string|object $context
     * @param  array|object $argv
     * @param  callback $callback
     * @return Zend_EventManager_ResponseCollection
     */
    public static function triggerUntil($event, $context, $argv, $callback)
    {
        return self::getEventCollection()->triggerUntil($event, $context, $argv, $callback);
    }

    /**
     * Attach a listener to an event
     *
     * @param  string $event
     * @param  callback $callback
     * @param  int $priority
     * @return Zend_Stdlib_CallbackHandler
     */
    public static function attach($event, $callback, $priority = 1)
    {
        return self::getEventCollection()->attach($event, $callback, $priority);
    }

    /**
     * Detach a callback from a listener
     *
     * @param  Zend_Stdlib_CallbackHandler $listener
     * @return bool
     */
    public static function detach(Zend_Stdlib_CallbackHandler $listener)
    {
        return self::getEventCollection()->detach($listener);
    }

    /**
     * Retrieve list of events this object manages
     *
     * @return array
     */
    public static function getEvents()
    {
        return self::getEventCollection()->getEvents();
    }

    /**
     * Retrieve all listeners for a given event
     *
     * @param  string $event
     * @return Zend_Stdlib_PriorityQueue|array
     */
    public static function getListeners($event)
    {
        return self::getEventCollection()->getListeners($event);
    }

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public static function clearListeners($event)
    {
        return self::getEventCollection()->clearListeners($event);
    }
}
