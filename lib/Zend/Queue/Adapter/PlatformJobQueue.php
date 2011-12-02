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
 * @version    $Id: PlatformJobQueue.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue_Adapter_AdapterAbstract
 */
#require_once 'Zend/Queue/Adapter/AdapterAbstract.php';

/**
 * Zend Platform JobQueue adapter
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_PlatformJobQueue extends Zend_Queue_Adapter_AdapterAbstract
{
    /**
     * @var ZendApi_JobQueue
     */
    protected $_zendQueue;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @param  Zend_Queue|null $queue
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        parent::__construct($options, $queue);

        if (!extension_loaded("jobqueue_client")) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Platform Job Queue extension does not appear to be loaded');
        }

        if (! isset($this->_options['daemonOptions'])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Job Queue host and password should be provided');
        }

        $options = $this->_options['daemonOptions'];

        if (!array_key_exists('host', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Platform Job Queue host should be provided');
        }
        if (!array_key_exists('password', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Platform Job Queue password should be provided');
        }

        $this->_zendQueue = new ZendApi_Queue($options['host']);

        if (!$this->_zendQueue) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Platform Job Queue connection failed');
        }
        if (!$this->_zendQueue->login($options['password'])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Job Queue login failed');
        }

        if ($this->_queue) {
            $this->_queue->setMessageClass('Zend_Queue_Message_PlatformJob');
        }
    }

    /********************************************************************
     * Queue management functions
     ********************************************************************/

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
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue|null $queue
     * @return integer
     */
    public function count(Zend_Queue $queue = null)
    {
        if ($queue !== null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue parameter is not supported');
        }

        return $this->_zendQueue->getNumOfJobsInQueue();
    }

    /********************************************************************
     * Messsage management functions
     ********************************************************************/

    /**
     * Send a message to the queue
     *
     * @param  array | ZendAPI_job $message Message to send to the active queue
     * @param  Zend_Queue $queue     Not supported
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message, Zend_Queue $queue = null)
    {
        if ($queue !== null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue parameter is not supported');
        }

        // This adapter can work only for this message type
        $classname = $this->_queue->getMessageClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }

        if ($message instanceof ZendAPI_Job) {
            $message = array('data' => $message);
        }

        $zendApiJob = new $classname($message);

        // Unfortunately, the Platform JQ API is PHP4-style...
        $platformJob = $zendApiJob->getJob();

        $jobId = $this->_zendQueue->addJob($platformJob);

        if (!$jobId) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Failed to add a job to queue: '
                . $this->_zendQueue->getLastError());
        }

        $zendApiJob->setJobId($jobId);
        return $zendApiJob;
    }

    /**
     * Get messages in the queue
     *
     * @param  integer    $maxMessages  Maximum number of messages to return
     * @param  integer    $timeout      Ignored
     * @param  Zend_Queue $queue        Not supported
     * @throws Zend_Queue_Exception
     * @return ArrayIterator
     */
    public function receive($maxMessages = null, $timeout = null, Zend_Queue $queue = null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        if ($queue !== null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue shouldn\'t be set');
        }

        $jobs = $this->_zendQueue->getJobsInQueue(null, $maxMessages, true);

        $classname = $this->_queue->getMessageClass();
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }

        $options = array(
            'queue'        => $this->_queue,
            'data'         => $jobs,
            'messageClass' => $this->_queue->getMessageClass(),
        );

        $classname = $this->_queue->getMessageSetClass();

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
     * @throws Zend_Queue_Exception
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
        if (get_class($message) != $this->_queue->getMessageClass()) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception(
                'Failed to remove job from the queue; only messages of type '
                . 'Zend_Queue_Message_PlatformJob may be used'
            );
        }

        return $this->_zendQueue->removeJob($message->getJobId());
    }

    public function isJobIdExist($id)
    {
         return (($this->_zendQueue->getJob($id))? true : false);
    }

    /********************************************************************
     * Supporting functions
     ********************************************************************/

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
            'create'                => false,
            'delete'                => false,
            'getQueues'             => false,
            'isExists'              => false,
            'count'                 => true,
            'send'                  => true,
            'receive'               => true,
            'deleteMessage'         => true,
        );
    }

    /********************************************************************
     * Functions that are not part of the Zend_Queue_Adapter_AdapterAbstract
     ********************************************************************/

    /**
     * Serialize
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_options');
    }

    /**
     * Unserialize
     *
     * @return void
     */
    public function __wakeup()
    {
        $options = $this->_options['daemonOptions'];

        $this->_zendQueue = new ZendApi_Queue($options['host']);

        if (!$this->_zendQueue) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Platform Job Queue connection failed');
        }
        if (!$this->_zendQueue->login($options['password'])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Job Queue login failed');
        }
    }
}
