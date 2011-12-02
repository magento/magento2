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
 * @package    Zend_Queue
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Iterator.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Message_Iterator implements Iterator, Countable
{
    /**
     * The data for the queue message
     *
     * @var array
     */
    protected $_data = array();

     /**
     * Connected is true if we have a reference to a live
     * Zend_Queue_Adapter_AdapterInterface object.
     * This is false after the Message has been deserialized.
     *
     * @var boolean
     */
    protected $_connected = true;

    /**
     * Zend_Queue_Adapter_AdapterInterface parent class or instance
     *
     * @var Zend_Queue_Adapter_AdapterInterface
     */
    protected $_queue = null;

    /**
     * Name of the class of the Zend_Queue_Adapter_AdapterInterface object.
     *
     * @var string
     */
    protected $_queueClass = null;

    /**
     * Zend_Queue_Message class name
     *
     * @var string
     */
    protected $_messageClass = 'Zend_Queue_Message';

     /**
     * Iterator pointer.
     *
     * @var integer
     */
    protected $_pointer = 0;

    /**
     * Constructor
     *
     * @param  array $options ('queue', 'messageClass', 'data'=>array());
     * @return void
     */
    public function __construct(array $options = array())
    {
        if (isset($options['queue'])) {
            $this->_queue      = $options['queue'];
            $this->_queueClass = get_class($this->_queue);
            $this->_connected  = true;
        } else {
            $this->_connected = false;
        }
        if (isset($options['messageClass'])) {
            $this->_messageClass = $options['messageClass'];
        }

        if (!is_array($options['data'])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('array optionsuration must have $options[\'data\'] = array');
        }

        // load the message class
        $classname = $this->_messageClass;
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }

        // for each of the messages
        foreach ($options['data'] as $data) {
            // construct the message parameters
            $message = array('data' => $data);

            // If queue has not been set, then use the default.
            if (empty($message['queue'])) {
                $message['queue'] = $this->_queue;
            }

            // construct the message and add it to _data[];
            $this->_data[] = new $classname($message);
        }
    }

    /**
     * Store queue and data in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_data', '_queueClass', '_messageClass', '_pointer');
    }

    /**
     * Setup to do on wakeup.
     * A de-serialized Message should not be assumed to have access to a live
     * queue connection, so set _connected = false.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_connected = false;
    }

    /**
     * Returns all data as an array.
     *
     * Used for debugging.
     *
     * @return array
     */
    public function toArray()
    {
        // @todo This works only if we have iterated through
        // the result set once to instantiate the messages.
        foreach ($this->_data as $i => $message) {
            $this->_data[$i] = $message->toArray();
        }
        return $this->_data;
    }

    /**
     * Returns the queue object, or null if this is disconnected message set
     *
     * @return Zend_Queue|null
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * Set the queue object, to re-establish a live connection
     * to the queue for a Message that has been de-serialized.
     *
     * @param  Zend_Queue_Adapter_AdapterInterface $queue
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function setQueue(Zend_Queue $queue)
    {
        $this->_queue     = $queue;
        $this->_connected = false;

        // @todo This works only if we have iterated through
        // the result set once to instantiate the rows.
        foreach ($this->_data as $i => $message) {
            $this->_connected = $this->_connected || $message->setQueue($queue);
        }

        return $this->_connected;
    }

    /**
     * Query the class name of the Queue object for which this
     * Message was created.
     *
     * @return string
     */
    public function getQueueClass()
    {
        return $this->_queueClass;
    }

    /*
     * Iterator implementation
     */

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Zend_Queue_Message current element from the collection
     */
    public function current()
    {
        return (($this->valid() === false)
            ? null
            : $this->_data[$this->_pointer]); // return the messages object
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return integer
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function next()
    {
        ++$this->_pointer;
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    public function valid()
    {
        return $this->_pointer < count($this);
    }

    /*
     * Countable Implementation
     */

    /**
     * Returns the number of elements in the collection.
     *
     * Implements Countable::count()
     *
     * @return integer
     */
    public function count()
    {
        return count($this->_data);
    }
}
