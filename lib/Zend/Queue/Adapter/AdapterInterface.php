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
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AdapterInterface.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Interface for common queue operations
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Queue_Adapter_AdapterInterface
{
    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @param  Zend_Queue $queue
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null);

    /**
     * Retrieve queue instance
     *
     * @return Zend_Queue
     */
    public function getQueue();

    /**
     * Set queue instnace
     *
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Adapter_AdapterInterface
     */
    public function setQueue(Zend_Queue $queue);

    /**
     * Does a queue already exist?
     *
     * Use isSupported('isExists') to determine if an adapter can test for
     * queue existance.
     *
     * @param  string $name Queue name
     * @return boolean
     */
    public function isExists($name);

    /**
     * Create a new queue
     *
     * Visibility timeout is how long a message is left in the queue
     * "invisible" to other readers.  If the message is acknowleged (deleted)
     * before the timeout, then the message is deleted.  However, if the
     * timeout expires then the message will be made available to other queue
     * readers.
     *
     * @param  string  $name Queue name
     * @param  integer $timeout Default visibility timeout
     * @return boolean
     */
    public function create($name, $timeout=null);

    /**
     * Delete a queue and all of its messages
     *
     * Return false if the queue is not found, true if the queue exists.
     *
     * @param  string $name Queue name
     * @return boolean
     */
    public function delete($name);

    /**
     * Get an array of all available queues
     *
     * Not all adapters support getQueues(); use isSupported('getQueues')
     * to determine if the adapter supports this feature.
     *
     * @return array
     */
    public function getQueues();

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue|null $queue
     * @return integer
     */
    public function count(Zend_Queue $queue = null);

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  mixed $message Message to send to the active queue
     * @param  Zend_Queue|null $queue
     * @return Zend_Queue_Message
     */
    public function send($message, Zend_Queue $queue = null);

    /**
     * Get messages in the queue
     *
     * @param  integer|null $maxMessages Maximum number of messages to return
     * @param  integer|null $timeout Visibility timeout for these messages
     * @param  Zend_Queue|null $queue
     * @return Zend_Queue_Message_Iterator
     */
    public function receive($maxMessages = null, $timeout = null, Zend_Queue $queue = null);

    /**
     * Delete a message from the queue
     *
     * Return true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     */
    public function deleteMessage(Zend_Queue_Message $message);

    /********************************************************************
     * Supporting functions
     *********************************************************************/

    /**
     * Returns the configuration options in this adapter.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Return a list of queue capabilities functions
     *
     * $array['function name'] = true or false
     * true is supported, false is not supported.
     *
     * @return array
     */
    public function getCapabilities();

    /**
     * Indicates if a function is supported or not.
     *
     * @param  string $name Function name
     * @return boolean
     */
    public function isSupported($name);
}
