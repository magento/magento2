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

#require_once 'Zend/EventManager/Event.php';
#require_once 'Zend/EventManager/EventCollection.php';
#require_once 'Zend/EventManager/ResponseCollection.php';
#require_once 'Zend/EventManager/SharedEventCollectionAware.php';
#require_once 'Zend/EventManager/StaticEventManager.php';
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
class Zend_EventManager_EventManager implements Zend_EventManager_EventCollection, Zend_EventManager_SharedEventCollectionAware
{
    /**
     * Subscribed events and their listeners
     * @var array Array of Zend_Stdlib_PriorityQueue objects
     */
    protected $events = array();

    /**
     * @var string Class representing the event being emitted
     */
    protected $eventClass = 'Zend_EventManager_Event';

    /**
     * Identifiers, used to pull static signals from StaticEventManager
     * @var array
     */
    protected $identifiers = array();

    /**
     * Static collections
     * @var false|null|Zend_EventManager_StaticEventCollection
     */
    protected $sharedCollections = null;

    /**
     * Constructor
     *
     * Allows optionally specifying identifier(s) to use to pull signals from a
     * StaticEventManager.
     *
     * @param  null|string|int|array|Traversable $identifiers
     * @return void
     */
    public function __construct($identifiers = null)
    {
        $this->setIdentifiers($identifiers);
    }

    /**
     * Set the event class to utilize
     *
     * @param  string $class
     * @return Zend_EventManager_EventManager
     */
    public function setEventClass($class)
    {
        $this->eventClass = $class;
        return $this;
    }

    /**
     * Set static collections container
     *
     * @param  Zend_EventManager_SharedEventCollection $collections
     * @return $this
     */
    public function setSharedCollections(Zend_EventManager_SharedEventCollection $collections)
    {
        $this->sharedCollections = $collections;
        return $this;
    }

    /**
     * Remove any shared collections
     *
     * Sets {@link $sharedCollections} to boolean false to disable ability
     * to lazy-load static event manager instance.
     *
     * @return void
     */
    public function unsetSharedCollections()
    {
        $this->sharedCollections = false;
    }

    /**
     * Get static collections container
     *
     * @return false|Zend_EventManager_SharedEventCollection
     */
    public function getSharedCollections()
    {
        if (null === $this->sharedCollections) {
            $this->setSharedCollections(Zend_EventManager_StaticEventManager::getInstance());
        }
        return $this->sharedCollections;
    }

    /**
     * Get the identifier(s) for this Zend_EventManager_EventManager
     *
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    /**
     * Set the identifiers (overrides any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return Zend_EventManager_EventManager
     */
    public function setIdentifiers($identifiers)
    {
        if (is_array($identifiers) || $identifiers instanceof Traversable) {
            $this->identifiers = array_unique((array) $identifiers);
        } elseif ($identifiers !== null) {
            $this->identifiers = array($identifiers);
        }
        return $this;
    }

    /**
     * Add some identifier(s) (appends to any currently set identifiers)
     *
     * @param string|int|array|Traversable $identifiers
     * @return Zend_EventManager_EventManager
     */
    public function addIdentifiers($identifiers)
    {
        if (is_array($identifiers) || $identifiers instanceof Traversable) {
            $this->identifiers = array_unique($this->identifiers + (array) $identifiers);
        } elseif ($identifiers !== null) {
            $this->identifiers = array_unique(array_merge($this->identifiers, array($identifiers)));
        }
        return $this;
    }

    /**
     * Trigger all listeners for a given event
     *
     * Can emulate triggerUntil() if the last argument provided is a callback.
     *
     * @param  string $event
     * @param  string|object $target Object calling emit, or symbol describing target (such as static method name)
     * @param  array|ArrayAccess $argv Array of arguments; typically, should be associative
     * @param  null|callback $callback
     * @return Zend_EventManager_ResponseCollection All listener return values
     */
    public function trigger($event, $target = null, $argv = array(), $callback = null)
    {
        if ($event instanceof Zend_EventManager_EventDescription) {
            $e        = $event;
            $event    = $e->getName();
            $callback = $target;
        } elseif ($target instanceof Zend_EventManager_EventDescription) {
            $e = $target;
            $e->setName($event);
            $callback = $argv;
        } elseif ($argv instanceof Zend_EventManager_EventDescription) {
            $e = $argv;
            $e->setName($event);
            $e->setTarget($target);
        } else {
            $e = new $this->eventClass();
            $e->setName($event);
            $e->setTarget($target);
            $e->setParams($argv);
        }

        if ($callback && !is_callable($callback)) {
            #require_once 'Zend/Stdlib/Exception/InvalidCallbackException.php';
            throw new Zend_Stdlib_Exception_InvalidCallbackException('Invalid callback provided');
        }

        return $this->triggerListeners($event, $e, $callback);
    }

    /**
     * Trigger listeners until return value of one causes a callback to
     * evaluate to true
     *
     * Triggers listeners until the provided callback evaluates the return
     * value of one as true, or until all listeners have been executed.
     *
     * @param  string $event
     * @param  string|object $target Object calling emit, or symbol describing target (such as static method name)
     * @param  array|ArrayAccess $argv Array of arguments; typically, should be associative
     * @param  Callable $callback
     * @throws Zend_Stdlib_Exception_InvalidCallbackException if invalid callback provided
     */
    public function triggerUntil($event, $target, $argv = null, $callback = null)
    {
        if ($event instanceof Zend_EventManager_EventDescription) {
            $e        = $event;
            $event    = $e->getName();
            $callback = $target;
        } elseif ($target instanceof Zend_EventManager_EventDescription) {
            $e = $target;
            $e->setName($event);
            $callback = $argv;
        } elseif ($argv instanceof Zend_EventManager_EventDescription) {
            $e = $argv;
            $e->setName($event);
            $e->setTarget($target);
        } else {
            $e = new $this->eventClass();
            $e->setName($event);
            $e->setTarget($target);
            $e->setParams($argv);
        }

        if (!is_callable($callback)) {
            #require_once 'Zend/Stdlib/Exception/InvalidCallbackException.php';
            throw new Zend_Stdlib_Exception_InvalidCallbackException('Invalid callback provided');
        }

        return $this->triggerListeners($event, $e, $callback);
    }

    /**
     * Attach a listener to an event
     *
     * The first argument is the event, and the next argument describes a
     * callback that will respond to that event. A CallbackHandler instance
     * describing the event listener combination will be returned.
     *
     * The last argument indicates a priority at which the event should be
     * executed. By default, this value is 1; however, you may set it for any
     * integer value. Higher values have higher priority (i.e., execute first).
     *
     * You can specify "*" for the event name. In such cases, the listener will
     * be triggered for every event.
     *
     * @param  string|array|Zend_EventManager_ListenerAggregate $event An event or array of event names. If a ListenerAggregate, proxies to {@link attachAggregate()}.
     * @param  callback|int $callback If string $event provided, expects PHP callback; for a ListenerAggregate $event, this will be the priority
     * @param  int $priority If provided, the priority at which to register the callback
     * @return Zend_Stdlib_CallbackHandler|mixed CallbackHandler if attaching callback (to allow later unsubscribe); mixed if attaching aggregate
     */
    public function attach($event, $callback = null, $priority = 1)
    {
        // Proxy ListenerAggregate arguments to attachAggregate()
        if ($event instanceof Zend_EventManager_ListenerAggregate) {
            return $this->attachAggregate($event, $callback);
        }

        // Null callback is invalid
        if (null === $callback) {
            #require_once 'Zend/EventManager/Exception/InvalidArgumentException.php';
            throw new Zend_EventManager_Exception_InvalidArgumentException(sprintf(
                '%s: expects a callback; none provided',
                __METHOD__
            ));
        }

        // Array of events should be registered individually, and return an array of all listeners
        if (is_array($event)) {
            $listeners = array();
            foreach ($event as $name) {
                $listeners[] = $this->attach($name, $callback, $priority);
            }
            return $listeners;
        }

        // If we don't have a priority queue for the event yet, create one
        if (empty($this->events[$event])) {
            $this->events[$event] = new Zend_Stdlib_PriorityQueue();
        }

        // Create a callback handler, setting the event and priority in its metadata
        $listener = new Zend_Stdlib_CallbackHandler($callback, array('event' => $event, 'priority' => $priority));

        // Inject the callback handler into the queue
        $this->events[$event]->insert($listener, $priority);
        return $listener;
    }

    /**
     * Attach a listener aggregate
     *
     * Listener aggregates accept an EventCollection instance, and call attach()
     * one or more times, typically to attach to multiple events using local
     * methods.
     *
     * @param  Zend_EventManager_ListenerAggregate $aggregate
     * @param  int $priority If provided, a suggested priority for the aggregate to use
     * @return mixed return value of {@link Zend_EventManager_ListenerAggregate::attach()}
     */
    public function attachAggregate(Zend_EventManager_ListenerAggregate $aggregate, $priority = 1)
    {
        return $aggregate->attach($this, $priority);
    }

    /**
     * Unsubscribe a listener from an event
     *
     * @param  Zend_Stdlib_CallbackHandler|Zend_EventManager_ListenerAggregate $listener
     * @return bool Returns true if event and listener found, and unsubscribed; returns false if either event or listener not found
     * @throws Zend_EventManager_Exception_InvalidArgumentException if invalid listener provided
     */
    public function detach($listener)
    {
        if ($listener instanceof Zend_EventManager_ListenerAggregate) {
            return $this->detachAggregate($listener);
        }

        if (!$listener instanceof Zend_Stdlib_CallbackHandler) {
            #require_once 'Zend/EventManager/Exception/InvalidArgumentException.php';
            throw new Zend_EventManager_Exception_InvalidArgumentException(sprintf(
                '%s: expected a Zend_EventManager_ListenerAggregate or Zend_Stdlib_CallbackHandler; received "%s"',
                __METHOD__,
                (is_object($listener) ? get_class($listener) : gettype($listener))
            ));
        }

        $event = $listener->getMetadatum('event');
        if (!$event || empty($this->events[$event])) {
            return false;
        }
        $return = $this->events[$event]->remove($listener);
        if (!$return) {
            return false;
        }
        if (!count($this->events[$event])) {
            unset($this->events[$event]);
        }
        return true;
    }

    /**
     * Detach a listener aggregate
     *
     * Listener aggregates accept an EventCollection instance, and call detach()
     * of all previously attached listeners.
     *
     * @param  Zend_EventManager_ListenerAggregate $aggregate
     * @return mixed return value of {@link Zend_EventManager_ListenerAggregate::detach()}
     */
    public function detachAggregate(Zend_EventManager_ListenerAggregate $aggregate)
    {
        return $aggregate->detach($this);
    }

    /**
     * Retrieve all registered events
     *
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->events);
    }

    /**
     * Retrieve all listeners for a given event
     *
     * @param  string $event
     * @return Zend_Stdlib_PriorityQueue
     */
    public function getListeners($event)
    {
        if (!array_key_exists($event, $this->events)) {
            return new Zend_Stdlib_PriorityQueue();
        }
        return $this->events[$event];
    }

    /**
     * Clear all listeners for a given event
     *
     * @param  string $event
     * @return void
     */
    public function clearListeners($event)
    {
        if (!empty($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * Prepare arguments
     *
     * Use this method if you want to be able to modify arguments from within a
     * listener. It returns an ArrayObject of the arguments, which may then be
     * passed to trigger() or triggerUntil().
     *
     * @param  array $args
     * @return ArrayObject
     */
    public function prepareArgs(array $args)
    {
        return new ArrayObject($args);
    }

    /**
     * Trigger listeners
     *
     * Actual functionality for triggering listeners, to which both trigger() and triggerUntil()
     * delegate.
     *
     * @param  string           $event Event name
     * @param  EventDescription $e
     * @param  null|callback    $callback
     * @return ResponseCollection
     */
    protected function triggerListeners($event, Zend_EventManager_EventDescription $e, $callback = null)
    {
        $responses = new Zend_EventManager_ResponseCollection;
        $listeners = $this->getListeners($event);

        // Add shared/wildcard listeners to the list of listeners,
        // but don't modify the listeners object
        $sharedListeners         = $this->getSharedListeners($event);
        $sharedWildcardListeners = $this->getSharedListeners('*');
        $wildcardListeners       = $this->getListeners('*');
        if (count($sharedListeners) || count($sharedWildcardListeners) || count($wildcardListeners)) {
            $listeners = clone $listeners;
        }

        // Shared listeners on this specific event
        $this->insertListeners($listeners, $sharedListeners);

        // Shared wildcard listeners
        $this->insertListeners($listeners, $sharedWildcardListeners);

        // Add wildcard listeners
        $this->insertListeners($listeners, $wildcardListeners);

        if ($listeners->isEmpty()) {
            return $responses;
        }

        foreach ($listeners as $listener) {
            // Trigger the listener's callback, and push its result onto the
            // response collection
            $responses->push(call_user_func($listener->getCallback(), $e));

            // If the event was asked to stop propagating, do so
            if ($e->propagationIsStopped()) {
                $responses->setStopped(true);
                break;
            }

            // If the result causes our validation callback to return true,
            // stop propagation
            if ($callback && call_user_func($callback, $responses->last())) {
                $responses->setStopped(true);
                break;
            }
        }

        return $responses;
    }

    /**
     * Get list of all listeners attached to the shared collection for
     * identifiers registered by this instance
     *
     * @param  string $event
     * @return array
     */
    protected function getSharedListeners($event)
    {
        if (!$sharedCollections = $this->getSharedCollections()) {
            return array();
        }

        $identifiers     = $this->getIdentifiers();
        $sharedListeners = array();

        foreach ($identifiers as $id) {
            if (!$listeners = $sharedCollections->getListeners($id, $event)) {
                continue;
            }

            if (!is_array($listeners) && !($listeners instanceof Traversable)) {
                continue;
            }

            foreach ($listeners as $listener) {
                if (!$listener instanceof Zend_Stdlib_CallbackHandler) {
                    continue;
                }
                $sharedListeners[] = $listener;
            }
        }

        return $sharedListeners;
    }

    /**
     * Add listeners to the master queue of listeners
     *
     * Used to inject shared listeners and wildcard listeners.
     *
     * @param  Zend_Stdlib_PriorityQueue $masterListeners
     * @param  Zend_Stdlib_PriorityQueue $listeners
     * @return void
     */
    protected function insertListeners($masterListeners, $listeners)
    {
        if (!count($listeners)) {
            return;
        }

        foreach ($listeners as $listener) {
            $priority = $listener->getMetadatum('priority');
            if (null === $priority) {
                $priority = 1;
            } elseif (is_array($priority)) {
                // If we have an array, likely using PriorityQueue. Grab first
                // element of the array, as that's the actual priority.
                $priority = array_shift($priority);
            }
            $masterListeners->insert($listener, $priority);
        }
    }
}
