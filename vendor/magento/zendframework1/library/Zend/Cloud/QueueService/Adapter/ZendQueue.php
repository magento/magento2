<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/QueueService/Adapter/AbstractAdapter.php';
#require_once 'Zend/Cloud/QueueService/Exception.php';
#require_once 'Zend/Queue.php';

/**
 * WindowsAzure adapter for simple queue service.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage QueueService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_QueueService_Adapter_ZendQueue
    extends Zend_Cloud_QueueService_Adapter_AbstractAdapter
{
    /**
     * Options array keys for the Zend_Queue adapter.
     */
    const ADAPTER = 'adapter';

    /**
     * Storage client
     *
     * @var Zend_Queue
     */
    protected $_queue = null;

    /**
     * @var array All queues
     */
    protected $_queues = array();

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct ($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_QueueService_Exception('Invalid options provided');
        }

        if (isset($options[self::MESSAGE_CLASS])) {
            $this->setMessageClass($options[self::MESSAGE_CLASS]);
        }

        if (isset($options[self::MESSAGESET_CLASS])) {
            $this->setMessageSetClass($options[self::MESSAGESET_CLASS]);
        }

        // Build Zend_Service_WindowsAzure_Storage_Blob instance
        if (!isset($options[self::ADAPTER])) {
            throw new Zend_Cloud_QueueService_Exception('No Zend_Queue adapter provided');
        } else {
            $adapter = $options[self::ADAPTER];
            unset($options[self::ADAPTER]);
        }
        try {
            $this->_queue = new Zend_Queue($adapter, $options);
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on create: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create a queue. Returns the ID of the created queue (typically the URL).
     * It may take some time to create the queue. Check your vendor's
     * documentation for details.
     *
     * @param  string $name
     * @param  array  $options
     * @return string Queue ID (typically URL)
     */
    public function createQueue($name, $options = null)
    {
        try {
            $this->_queues[$name] = $this->_queue->createQueue($name, isset($options[Zend_Queue::TIMEOUT])?$options[Zend_Queue::TIMEOUT]:null);
            return $name;
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue creation: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete a queue. All messages in the queue will also be deleted.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return boolean true if successful, false otherwise
     */
    public function deleteQueue($queueId, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            return false;
        }
        try {
            if ($this->_queues[$queueId]->deleteQueue()) {
                unset($this->_queues[$queueId]);
                return true;
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on queue deletion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List all queues.
     *
     * @param  array $options
     * @return array Queue IDs
     */
    public function listQueues($options = null)
    {
        try {
            return $this->_queue->getQueues();
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on listing queues: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get a key/value array of metadata for the given queue.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return array
     */
    public function fetchQueueMetadata($queueId, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            return false;
        }
        try {
            return $this->_queues[$queueId]->getOptions();
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on fetching queue metadata: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Store a key/value array of metadata for the specified queue.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath. Some adapters may not support this method.
     *
     * @param  string $queueId
     * @param  array  $metadata
     * @param  array  $options
     * @return void
     */
    public function storeQueueMetadata($queueId, $metadata, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            throw new Zend_Cloud_QueueService_Exception("No such queue: $queueId");
        }
        try {
            return $this->_queues[$queueId]->setOptions($metadata);
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on setting queue metadata: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Send a message to the specified queue.
     *
     * @param  string $queueId
     * @param  string $message
     * @param  array  $options
     * @return string Message ID
     */
    public function sendMessage($queueId, $message, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            throw new Zend_Cloud_QueueService_Exception("No such queue: $queueId");
        }
        try {
            return $this->_queues[$queueId]->send($message);
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on sending message: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Recieve at most $max messages from the specified queue and return the
     * message IDs for messages recieved.
     *
     * @param  string $queueId
     * @param  int    $max
     * @param  array  $options
     * @return array
     */
    public function receiveMessages($queueId, $max = 1, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            throw new Zend_Cloud_QueueService_Exception("No such queue: $queueId");
        }
        try {
            $res = $this->_queues[$queueId]->receive($max, isset($options[Zend_Queue::TIMEOUT])?$options[Zend_Queue::TIMEOUT]:null);
            if ($res instanceof Iterator) {
                return $this->_makeMessages($res);
            } else {
                return $this->_makeMessages(array($res));
            }
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on recieving messages: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create Zend_Cloud_QueueService_Message array for
     * Azure messages.
     *
     * @param array $messages
     * @return Zend_Cloud_QueueService_Message[]
     */
    protected function _makeMessages($messages)
    {
        $messageClass = $this->getMessageClass();
        $setClass     = $this->getMessageSetClass();
        $result = array();
        foreach ($messages as $message) {
            $result[] = new $messageClass($message->body, $message);
        }
        return new $setClass($result);
    }

    /**
     * Delete the specified message from the specified queue.
     *
     * @param  string $queueId
     * @param  Zend_Cloud_QueueService_Message $message Message ID or message
     * @param  array  $options
     * @return void
     */
    public function deleteMessage($queueId, $message, $options = null)
    {
        if (!isset($this->_queues[$queueId])) {
            throw new Zend_Cloud_QueueService_Exception("No such queue: $queueId");
        }
        try {
            if ($message instanceof Zend_Cloud_QueueService_Message) {
                $message = $message->getMessage();
            }
            if (!($message instanceof Zend_Queue_Message)) {
                throw new Zend_Cloud_QueueService_Exception('Cannot delete the message: Zend_Queue_Message object required');
            }

            return $this->_queues[$queueId]->deleteMessage($message);
        } catch (Zend_Queue_Exception $e) {
            throw new Zend_Cloud_QueueService_Exception('Error on deleting a message: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Peek at the messages from the specified queue without removing them.
     *
     * @param  string $queueId
     * @param  int $num How many messages
     * @param  array  $options
     * @return Zend_Cloud_QueueService_Message[]
     */
    public function peekMessages($queueId, $num = 1, $options = null)
    {
        #require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('ZendQueue doesn\'t currently support message peeking');
    }

    /**
     * Get Azure implementation
     * @return Zend_Queue
     */
    public function getClient()
    {
        return $this->_queue;
    }
}
