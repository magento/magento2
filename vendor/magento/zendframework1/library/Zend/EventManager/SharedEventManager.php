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

#require_once 'Zend/EventManager/EventManager.php';
#require_once 'Zend/EventManager/SharedEventCollection.php';

/**
 * Shared/contextual EventManager
 *
 * Allows attaching to EMs composed by other classes without having an instance first.
 * The assumption is that the SharedEventManager will be injected into EventManager
 * instances, and then queried for additional listeners when triggering an event.
 *
 * @category   Zend
 * @package    Zend_EventManager
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_EventManager_SharedEventManager implements Zend_EventManager_SharedEventCollection
{
    /**
     * Identifiers with event connections
     * @var array
     */
    protected $identifiers = array();

    /**
     * Attach a listener to an event
     *
     * Allows attaching a callback to an event offerred by one or more
     * identifying components. As an example, the following connects to the
     * "getAll" event of both an AbstractResource and EntityResource:
     *
     * <code>
     * SharedEventManager::getInstance()->connect(
     *     array('My\Resource\AbstractResource', 'My\Resource\EntityResource'),
     *     'getOne',
     *     function ($e) use ($cache) {
     *         if (!$id = $e->getParam('id', false)) {
     *             return;
     *         }
     *         if (!$data = $cache->load(get_class($resource) . '::getOne::' . $id )) {
     *             return;
     *         }
     *         return $data;
     *     }
     * );
     * </code>
     *
     * @param  string|array $id Identifier(s) for event emitting component(s)
     * @param  string $event
     * @param  callback $callback PHP Callback
     * @param  int $priority Priority at which listener should execute
     * @return void
     */
    public function attach($id, $event, $callback, $priority = 1)
    {
        $ids = (array) $id;
        foreach ($ids as $id) {
            if (!array_key_exists($id, $this->identifiers)) {
                $this->identifiers[$id] = new Zend_EventManager_EventManager();
            }
            $this->identifiers[$id]->attach($event, $callback, $priority);
        }
    }

    /**
     * Detach a listener from an event offered by a given resource
     *
     * @param  string|int $id
     * @param  Zend_Stdlib_CallbackHandler $listener
     * @return bool Returns true if event and listener found, and unsubscribed; returns false if either event or listener not found
     */
    public function detach($id, Zend_Stdlib_CallbackHandler $listener)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }
        return $this->identifiers[$id]->detach($listener);
    }

    /**
     * Retrieve all registered events for a given resource
     *
     * @param  string|int $id
     * @return array
     */
    public function getEvents($id)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }
        return $this->identifiers[$id]->getEvents();
    }

    /**
     * Retrieve all listeners for a given identifier and event
     *
     * @param  string|int $id
     * @param  string|int $event
     * @return false|Zend_Stdlib_PriorityQueue
     */
    public function getListeners($id, $event)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }
        return $this->identifiers[$id]->getListeners($event);
    }

    /**
     * Clear all listeners for a given identifier, optionally for a specific event
     *
     * @param  string|int $id
     * @param  null|string $event
     * @return bool
     */
    public function clearListeners($id, $event = null)
    {
        if (!array_key_exists($id, $this->identifiers)) {
            return false;
        }

        if (null === $event) {
            unset($this->identifiers[$id]);
            return true;
        }

        return $this->identifiers[$id]->clearListeners($event);
    }
}
