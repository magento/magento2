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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Queue_Adapter_AdapterAbstract
 */
#require_once 'Zend/Queue/Adapter/AdapterAbstract.php';

/**
 * @see Zend_Db_Select
 */
#require_once 'Zend/Db/Select.php';

/**
 * @see Zend_Db
 */
#require_once 'Zend/Db.php';

/**
 * @see Zend_Queue_Adapter_Db_Queue
 */
#require_once 'Zend/Queue/Adapter/Db/Queue.php';

/**
 * @see Zend_Queue_Adapter_Db_Message
 */
#require_once 'Zend/Queue/Adapter/Db/Message.php';

/**
 * Class for using connecting to a Zend_Db-based queuing system
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Db extends Zend_Queue_Adapter_AdapterAbstract
{
    /**
     * @var Zend_Queue_Adapter_Db_Queue
     */
    protected $_queueTable = null;

    /**
     * @var Zend_Queue_Adapter_Db_Message
     */
    protected $_messageTable = null;

    /**
     * @var Zend_Db_Table_Row_Abstract
     */
    protected $_messageRow = null;

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

        if (!isset($this->_options['options'][Zend_Db_Select::FOR_UPDATE])) {
            // turn off auto update by default
            $this->_options['options'][Zend_Db_Select::FOR_UPDATE] = false;
        }

        if (!is_bool($this->_options['options'][Zend_Db_Select::FOR_UPDATE])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Options array item: Zend_Db_Select::FOR_UPDATE must be boolean');
        }

        if (isset($this->_options['dbAdapter'])
            && $this->_options['dbAdapter'] instanceof Zend_Db_Adapter_Abstract) {
            $db = $this->_options['dbAdapter'];
        } else {
            $db = $this->_initDbAdapter();
        }

        $this->_queueTable = new Zend_Queue_Adapter_Db_Queue(array(
            'db' => $db,
        ));

        $this->_messageTable = new Zend_Queue_Adapter_Db_Message(array(
            'db' => $db,
        ));

    }

    /**
     * Initialize Db adapter using 'driverOptions' section of the _options array
     *
     * Throws an exception if the adapter cannot connect to DB.
     *
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Queue_Exception
     */
    protected function _initDbAdapter()
    {
        $options = &$this->_options['driverOptions'];
        if (!array_key_exists('type', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Configuration array must have a key for 'type' for the database type to use");
        }

        if (!array_key_exists('host', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Configuration array must have a key for 'host' for the host to use");
        }

        if (!array_key_exists('username', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Configuration array must have a key for 'username' for the username to use");
        }

        if (!array_key_exists('password', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Configuration array must have a key for 'password' for the password to use");
        }

        if (!array_key_exists('dbname', $options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Configuration array must have a key for 'dbname' for the database to use");
        }

        $type = $options['type'];
        unset($options['type']);

        try {
            $db = Zend_Db::factory($type, $options);
        } catch (Zend_Db_Exception $e) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Error connecting to database: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $db;
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
        $id = 0;

        try {
            $id = $this->getQueueId($name);
        } catch (Zend_Queue_Exception $e) {
            return false;
        }

        return ($id > 0);
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
     * @throws Zend_Queue_Exception - database error
     */
    public function create($name, $timeout = null)
    {
        if ($this->isExists($name)) {
            return false;
        }

        $queue = $this->_queueTable->createRow();
        $queue->queue_name = $name;
        $queue->timeout    = ($timeout === null) ? self::CREATE_TIMEOUT_DEFAULT : (int)$timeout;

        try {
            if ($queue->save()) {
                return true;
            }
        } catch (Exception $e) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        return false;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param  string  $name queue name
     * @return boolean
     * @throws Zend_Queue_Exception - database error
     */
    public function delete($name)
    {
        $id = $this->getQueueId($name); // get primary key

        // if the queue does not exist then it must already be deleted.
        $list = $this->_queueTable->find($id);
        if (count($list) === 0) {
            return false;
        }
        $queue = $list->current();

        if ($queue instanceof Zend_Db_Table_Row_Abstract) {
            try {
                $queue->delete();
            } catch (Exception $e) {
                #require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (array_key_exists($name, $this->_queues)) {
            unset($this->_queues[$name]);
        }

        return true;
    }

    /*
     * Get an array of all available queues
     *
     * Not all adapters support getQueues(), use isSupported('getQueues')
     * to determine if the adapter supports this feature.
     *
     * @return array
     * @throws Zend_Queue_Exception - database error
     */
    public function getQueues()
    {
        $query = $this->_queueTable->select();
        $query->from($this->_queueTable, array('queue_id', 'queue_name'));

        $this->_queues = array();
        foreach ($this->_queueTable->fetchAll($query) as $queue) {
            $this->_queues[$queue->queue_name] = (int)$queue->queue_id;
        }

        $list = array_keys($this->_queues);

        return $list;
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue $queue
     * @return integer
     * @throws Zend_Queue_Exception
     */
    public function count(Zend_Queue $queue = null)
    {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $info  = $this->_messageTable->info();
        $db    = $this->_messageTable->getAdapter();
        $query = $db->select();
        $query->from($info['name'], array(new Zend_Db_Expr('COUNT(1)')))
              ->where('queue_id=?', $this->getQueueId($queue->getName()));

        // return count results
        return (int) $db->fetchOne($query);
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
     * @throws Zend_Queue_Exception - database error
     */
    public function send($message, Zend_Queue $queue = null)
    {
        if ($this->_messageRow === null) {
            $this->_messageRow = $this->_messageTable->createRow();
        }

        if ($queue === null) {
            $queue = $this->_queue;
        }

        if (is_scalar($message)) {
            $message = (string) $message;
        }
        if (is_string($message)) {
            $message = trim($message);
        }

        if (!$this->isExists($queue->getName())) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist:' . $queue->getName());
        }

        $msg           = clone $this->_messageRow;
        $msg->queue_id = $this->getQueueId($queue->getName());
        $msg->created  = time();
        $msg->body     = $message;
        $msg->md5      = md5($message);
        // $msg->timeout = ??? @TODO

        try {
            $msg->save();
        } catch (Exception $e) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $options = array(
            'queue' => $queue,
            'data'  => $msg->toArray(),
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
     * @throws Zend_Queue_Exception - database error
     */
    public function receive($maxMessages = null, $timeout = null, Zend_Queue $queue = null)
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

        $msgs      = array();
        $info      = $this->_messageTable->info();
        $microtime = microtime(true); // cache microtime
        $db        = $this->_messageTable->getAdapter();

        // start transaction handling
        try {
            if ( $maxMessages > 0 ) { // ZF-7666 LIMIT 0 clause not included.
                $db->beginTransaction();

                $query = $db->select();
                if ($this->_options['options'][Zend_Db_Select::FOR_UPDATE]) {
                    // turn on forUpdate
                    $query->forUpdate();
                }
                $query->from($info['name'], array('*'))
                      ->where('queue_id=?', $this->getQueueId($queue->getName()))
                      ->where('handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime)
                      ->limit($maxMessages);

                foreach ($db->fetchAll($query) as $data) {
                    // setup our changes to the message
                    $data['handle'] = md5(uniqid(rand(), true));

                    $update = array(
                        'handle'  => $data['handle'],
                        'timeout' => $microtime,
                    );

                    // update the database
                    $where   = array();
                    $where[] = $db->quoteInto('message_id=?', $data['message_id']);
                    $where[] = 'handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime;

                    $count = $db->update($info['name'], $update, $where);

                    // we check count to make sure no other thread has gotten
                    // the rows after our select, but before our update.
                    if ($count > 0) {
                        $msgs[] = $data;
                    }
                }
                $db->commit();
            }
        } catch (Exception $e) {
            $db->rollBack();

            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
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
     * @throws Zend_Queue_Exception - database error
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
        $db    = $this->_messageTable->getAdapter();
        $where = $db->quoteInto('handle=?', $message->handle);

        if ($this->_messageTable->delete($where)) {
            return true;
        }

        return false;
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
            'deleteMessage' => true,
            'getQueues'     => true,
            'count'         => true,
            'isExists'      => true,
        );
    }

    /********************************************************************
     * Functions that are not part of the Zend_Queue_Adapter_Abstract
     *********************************************************************/
    /**
     * Get the queue ID
     *
     * Returns the queue's row identifier.
     *
     * @param  string       $name
     * @return integer|null
     * @throws Zend_Queue_Exception
     */
    protected function getQueueId($name)
    {
        if (array_key_exists($name, $this->_queues)) {
            return $this->_queues[$name];
        }

        $query = $this->_queueTable->select();
        $query->from($this->_queueTable, array('queue_id'))
              ->where('queue_name=?', $name);

        $queue = $this->_queueTable->fetchRow($query);

        if ($queue === null) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Queue does not exist: ' . $name);
        }

        $this->_queues[$name] = (int)$queue->queue_id;

        return $this->_queues[$name];
    }
}
