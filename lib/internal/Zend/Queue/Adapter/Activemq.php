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
 * @version    $Id: Activemq.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue_Adapter_AdapterAbstract
 */
#require_once 'Zend/Queue/Adapter/AdapterAbstract.php';

/**
 * @see Zend_Queue_Adapter_Stomp_Client
 */
#require_once 'Zend/Queue/Stomp/Client.php';

/**
 * @see Zend_Queue_Adapter_Stomp_Frame
 */
#require_once 'Zend/Queue/Stomp/Frame.php';

/**
 * Class for using Stomp to talk to an Stomp compliant server
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Activemq extends Zend_Queue_Adapter_AdapterAbstract
{
    const DEFAULT_SCHEME = 'tcp';
    const DEFAULT_HOST   = '127.0.0.1';
    const DEFAULT_PORT   = 61613;

    /**
     * @var Zend_Queue_Adapter_Stomp_client
     */
    private $_client = null;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $config An array having configuration data
     * @param  Zend_Queue The Zend_Queue object that created this class
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        parent::__construct($options);

        $options = &$this->_options['driverOptions'];
        if (!array_key_exists('scheme', $options)) {
            $options['scheme'] = self::DEFAULT_SCHEME;
        }
        if (!array_key_exists('host', $options)) {
            $options['host'] = self::DEFAULT_HOST;
        }
        if (!array_key_exists('port', $options)) {
            $options['port'] = self::DEFAULT_PORT;
        }

        if (array_key_exists('stompClient', $options)) {
            $this->_client = $options['stompClient'];
        } else {
            $this->_client = new Zend_Queue_Stomp_Client($options['scheme'], $options['host'], $options['port']);
        }

        $connect = $this->_client->createFrame();

        // Username and password are optional on some messaging servers
        // such as Apache's ActiveMQ
        $connect->setCommand('CONNECT');
        if (isset($options['username'])) {
            $connect->setHeader('login', $options['username']);
            $connect->setHeader('passcode', $options['password']);
        }

        $response = $this->_client->send($connect)->receive();

        if ((false !== $response)
            && ($response->getCommand() != 'CONNECTED')
        ) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Unable to authenticate to '".$options['scheme'].'://'.$options['host'].':'.$options['port']."'");
        }
    }

    /**
     * Close the socket explicitly when destructed
     *
     * @return void
     */
    public function __destruct()
    {
        // Gracefully disconnect
        $frame = $this->_client->createFrame();
        $frame->setCommand('DISCONNECT');
        $this->_client->send($frame);
        unset($this->_client);
    }

    /**
     * Create a new queue
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visibility timeout
     * @return void
     * @throws Zend_Queue_Exception
     */
    public function create($name, $timeout=null)
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('create() is not supported in ' . get_class($this));
    }

    /**
     * Delete a queue and all of its messages
     *
     * @param  string $name queue name
     * @return void
     * @throws Zend_Queue_Exception
     */
    public function delete($name)
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('delete() is not supported in ' . get_class($this));
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
        $frame = $this->_client->createFrame();
        $frame->setCommand('ACK');
        $frame->setHeader('message-id', $message->handle);

        $this->_client->send($frame);

        return true;
    }

    /**
     * Get an array of all available queues
     *
     * @return void
     * @throws Zend_Queue_Exception
     */
    public function getQueues()
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('getQueues() is not supported in this adapter');
    }

    /**
     * Return the first element in the queue
     *
     * @param  integer    $maxMessages
     * @param  integer    $timeout
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message_Iterator
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

        // read
        $data = array();

        // signal that we are reading
        $frame = $this->_client->createFrame();
        $frame->setCommand('SUBSCRIBE');
        $frame->setHeader('destination', $queue->getName());
        $frame->setHeader('ack','client');
        $this->_client->send($frame);

        if ($maxMessages > 0) {
            if ($this->_client->canRead()) {
                for ($i = 0; $i < $maxMessages; $i++) {
                    $response = $this->_client->receive();

                    switch ($response->getCommand()) {
                        case 'MESSAGE':
                            $datum = array(
                                'message_id' => $response->getHeader('message-id'),
                                'handle'     => $response->getHeader('message-id'),
                                'body'       => $response->getBody(),
                                'md5'        => md5($response->getBody())
                            );
                            $data[] = $datum;
                            break;
                        default:
                            $block = print_r($response, true);
                            #require_once 'Zend/Queue/Exception.php';
                            throw new Zend_Queue_Exception('Invalid response received: ' . $block);
                    }
                }
            }
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $data,
            'messageClass' => $queue->getMessageClass()
        );

        $classname = $queue->getMessageSetClass();

        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Push an element onto the end of the queue
     *
     * @param  string     $message message to send to the queue
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message
     */
    public function send($message, Zend_Queue $queue=null)
    {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $frame = $this->_client->createFrame();
        $frame->setCommand('SEND');
        $frame->setHeader('destination', $queue->getName());
        $frame->setHeader('content-length', strlen($message));
        $frame->setBody((string) $message);
        $this->_client->send($frame);

        $data = array(
            'message_id' => null,
            'body'       => $message,
            'md5'        => md5($message),
            'handle'     => null
        );

        $options = array(
            'queue' => $queue,
            'data'  => $data
        );

        $classname = $queue->getMessageClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Returns the length of the queue
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

    /**
     * Does a queue already exist?
     *
     * @param  string $name
     * @return boolean
     * @throws Zend_Queue_Exception (not supported)
     */
    public function isExists($name)
    {
        #require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('isExists() is not supported in this adapter');
    }

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
            'create'        => false,
            'delete'        => false,
            'send'          => true,
            'receive'       => true,
            'deleteMessage' => true,
            'getQueues'     => false,
            'count'         => false,
            'isExists'      => false,
        );
    }
}
