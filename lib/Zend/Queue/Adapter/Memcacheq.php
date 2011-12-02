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
 * @version    $Id: Memcacheq.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue_Adapter_AdapterAbstract
 */
#require_once 'Zend/Queue/Adapter/AdapterAbstract.php';

/**
 * Class for using connecting to a Zend_Cache-based queuing system
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Memcacheq extends Zend_Queue_Adapter_AdapterAbstract
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 22201;
    const EOL          = "\r\n";

    /**
     * @var Memcache
     */
    protected $_cache = null;

    /**
     * @var string
     */
    protected $_host = null;

    /**
     * @var integer
     */
    protected $_port = null;

    /**
     * @var resource
     */
    protected $_socket = null;

    /********************************************************************
    * Constructor / Destructor
     *********************************************************************/

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @param  null|Zend_Queue $queue
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        if (!extension_loaded('memcache')) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Memcache extension does not appear to be loaded');
        }

        parent::__construct($options, $queue);

        $options = &$this->_options['driverOptions'];

        if (!array_key_exists('host', $options)) {
            $options['host'] = self::DEFAULT_HOST;
        }
        if (!array_key_exists('port', $options)) {
            $options['port'] = self::DEFAULT_PORT;
        }

        $this->_cache = new Memcache();

        $result = $this->_cache->connect($options['host'], $options['port']);

        if ($result === false) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Could not connect to MemcacheQ');
        }

        $this->_host = $options['host'];
        $this->_port = (int)$options['port'];
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_cache instanceof Memcache) {
            $this->_cache->close();
        }
        if (is_resource($this->_socket)) {
            $cmd = 'quit' . self::EOL;
            fwrite($this->_socket, $cmd);
            fclose($this->_socket);
        }
    }

    /********************************************************************
     * Queue management functions
     *********************************************************************/

    /**
     * Does a queue already exist?
     *
     * Throws an exception if the adapter cannot determine if a queue exists.
     * use isSupported('isExists') to determine if an adapter can test for
     * queue existance.
     *
     * @param  string $name
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function isExists($name)
    {
        if (empty($this->_queues)) {
            $this->getQueues();
        }

        return in_array($name, $this->_queues);
    }

    /**
     * Create a new queue
     *
     * Visibility timeout is how long a message is left in the queue "invisible"
     * to other readers.  If the message is acknowleged (deleted) before the
     * timeout, then the message is deleted.  However, if the timeout expires
     * then the message will be made available to other queue readers.
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visibility timeout
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function create($name, $timeout=null)
    {
        if ($this->isExists($name)) {
            return false;
        }
        if ($timeout === null) {
            $timeout = self::CREATE_TIMEOUT_DEFAULT;
        }

        // MemcacheQ does not have a method to "create" a queue
        // queues are created upon sending a packet.
        // We cannot use the send() and receive() functions because those
        // depend on the current name.
        $result = $this->_cache->set($name, 'creating queue', 0, 15);
        $result = $this->_cache->get($name);

        $this->_queues[] = $name;

        return true;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param  string  $name queue name
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function delete($name)
    {
        $response = $this->_sendCommand('delete ' . $name, array('DELETED', 'NOT_FOUND'), true);

        if (in_array('DELETED', $response)) {
            $key = array_search($name, $this->_queues);

            if ($key !== false) {
                unset($this->_queues[$key]);
            }
            return true;
        }

        return false;
    }

    /**
     * Get an array of all available queues
     *
     * Not all adapters support getQueues(), use isSupported('getQueues')
     * to determine if the adapter supports this feature.
     *
     * @return array
     * @throws Zend_Queue_Exception
     */
    public function getQueues()
    {
        $this->_queues = array();

        $response = $this->_sendCommand('stats queue', array('END'));

        foreach ($response as $i => $line) {
            $this->_queues[] = str_replace('STAT ', '', $line);
        }

        return $this->_queues;
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue $queue
     * @return integer
     * @throws Zend_Queue_Exception (not supported)
     */
    public function count(Zend_Queue $queue=null)
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('count() is not supported in this adapter');
    }

    /********************************************************************
     * Messsage management functions
     *********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  string     $message Message to send to the active queue
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message, Zend_Queue $queue=null)
    {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        if (!$this->isExists($queue->getName())) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queue->getName());
        }

        $message = (string) $message;
        $data    = array(
            'message_id' => md5(uniqid(rand(), true)),
            'handle'     => null,
            'body'       => $message,
            'md5'        => md5($message),
        );

        $result = $this->_cache->set($queue->getName(), $message, 0, 0);
        if ($result === false) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('failed to insert message into queue:' . $queue->getName());
        }

        $options = array(
            'queue' => $queue,
            'data'  => $data,
        );

        $classname = $queue->getMessageClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Get messages in the queue
     *
     * @param  integer    $maxMessages  Maximum number of messages to return
     * @param  integer    $timeout      Visibility timeout for these messages
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message_Iterator
     * @throws Zend_Queue_Exception
     */
    public function receive($maxMessages=null, $timeout=null, Zend_Queue $queue=null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        if ($timeout === null) {
            $timeout = self::RECEIVE_TIMEOUT_DEFAULT;
        }
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $msgs = array();
        if ($maxMessages > 0 ) {
            for ($i = 0; $i < $maxMessages; $i++) {
                $data = array(
                    'handle' => md5(uniqid(rand(), true)),
                    'body'   => $this->_cache->get($queue->getName()),
                );

                $msgs[] = $data;
            }
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $msgs,
            'messageClass' => $queue->getMessageClass(),
        );

        $classname = $queue->getMessageSetClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     * @throws Zend_Queue_Exception (unsupported)
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('deleteMessage() is not supported in  ' . get_class($this));
    }

    /********************************************************************
     * Supporting functions
     *********************************************************************/

    /**
     * Return a list of queue capabilities functions
     *
     * $array['function name'] = true or false
     * true is supported, false is not supported.
     *
     * @param  string $name
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'create'        => true,
            'delete'        => true,
            'send'          => true,
            'receive'       => true,
            'deleteMessage' => false,
            'getQueues'     => true,
            'count'         => false,
            'isExists'      => true,
        );
    }

    /********************************************************************
     * Functions that are not part of the Zend_Queue_Adapter_Abstract
     *********************************************************************/

    /**
     * sends a command to MemcacheQ
     *
     * The memcache functions by php cannot handle all types of requests
     * supported by MemcacheQ
     * Non-standard requests are handled by this function.
     *
     * @param  string  $command - command to send to memcacheQ
     * @param  array   $terminator - strings to indicate end of memcacheQ response
     * @param  boolean $include_term - include terminator in response
     * @return array
     * @throws Zend_Queue_Exception if connection cannot be opened
     */
    protected function _sendCommand($command, array $terminator, $include_term=false)
    {
        if (!is_resource($this->_socket)) {
            $this->_socket = fsockopen($this->_host, $this->_port, $errno, $errstr, 10);
        }
        if ($this->_socket === false) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Could not open a connection to $this->_host:$this->_port errno=$errno : $errstr");
        }

        $response = array();

        $cmd = $command . self::EOL;
        fwrite($this->_socket, $cmd);

        $continue_reading = true;
        while (!feof($this->_socket) && $continue_reading) {
            $resp = trim(fgets($this->_socket, 1024));
            if (in_array($resp, $terminator)) {
                if ($include_term) {
                    $response[] = $resp;
                }
                $continue_reading = false;
            } else {
                $response[] = $resp;
            }
        }

        return $response;
    }
}
