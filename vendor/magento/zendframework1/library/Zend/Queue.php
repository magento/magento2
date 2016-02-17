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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class for connecting to queues performing common operations.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue implements Countable
{
    /**
     * Use the TIMEOUT constant in the config of a Zend_Queue
     */
    const TIMEOUT = 'timeout';

    /**
     * Default visibility passed to count
     */
    const VISIBILITY_TIMEOUT = 30;

    /**
     * Use the NAME constant in the config of Zend_Queue
     */
    const NAME = 'name';

    /**
     * @var Zend_Queue_Adapter_AdapterInterface
     */
    protected $_adapter = null;

    /**
     * User-provided configuration
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Zend_Queue_Message class
     *
     * @var string
     */
    protected $_messageClass = 'Zend_Queue_Message';

    /**
     * Zend_Queue_Message_Iterator class
     *
     * @var string
     */
    protected $_messageSetClass = 'Zend_Queue_Message_Iterator';

    /**
     * @var Zend_Log
     */
    protected $_logger = null;

    /**
     * Constructor
     *
     * Can be called as
     * $queue = new Zend_Queue($config);
     * - or -
     * $queue = new Zend_Queue('array', $config);
     * - or -
     * $queue = new Zend_Queue(null, $config); // Zend_Queue->createQueue();
     *
     * @param  string|Zend_Queue_Adapter|array|Zend_Config|null String or adapter instance, or options array or Zend_Config instance
     * @param  Zend_Config|array $options Zend_Config or a configuration array
     * @return void
     */
    public function __construct($spec, $options = array())
    {
        $adapter = null;
        if ($spec instanceof Zend_Queue_Adapter_AdapterInterface) {
            $adapter = $spec;
        } elseif (is_string($spec)) {
            $adapter = $spec;
        } elseif ($spec instanceof Zend_Config) {
            $options = $spec->toArray();
        } elseif (is_array($spec)) {
            $options = $spec;
        }

        // last minute error checking
        if ((null === $adapter)
            && (!is_array($options) && (!$options instanceof Zend_Config))
        ) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('No valid params passed to constructor');
        }

        // Now continue as we would if we were a normal constructor
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = array();
        }

        // Make sure we have some defaults to work with
        if (!isset($options[self::TIMEOUT])) {
            $options[self::TIMEOUT] = self::VISIBILITY_TIMEOUT;
        }

        // Make sure all defaults are appropriately set.
        if (!array_key_exists('timeout', $options)) {
            $options[self::TIMEOUT] = self::VISIBILITY_TIMEOUT;
        }
        if (array_key_exists('messageClass', $options)) {
            $this->setMessageClass($options['messageClass']);
        }
        if (array_key_exists('messageSetClass', $options)) {
            $this->setMessageSetClass($options['messageSetClass']);
        }

        $this->setOptions($options);

        // if we were passed an adapter we either build the $adapter or use it
        if (null !== $adapter) {
            $this->setAdapter($adapter);
        }
    }

    /**
     * Set queue options
     *
     * @param  array $options
     * @return Zend_Queue
     */
    public function setOptions(array $options)
    {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }

    /**
     * Set an individual configuration option
     *
     * @param  string $name
     * @param  mixed $value
     * @return Zend_Queue
     */
    public function setOption($name, $value)
    {
        $this->_options[(string) $name] = $value;
        return $this;
    }

    /**
     * Returns the configuration options for the queue
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Determine if a requested option has been defined
     *
     * @param  string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }

    /**
     * Retrieve a single option
     *
     * @param  string $name
     * @return null|mixed Returns null if option does not exist; option value otherwise
     */
    public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return null;
    }

    /**
     * Set the adapter for this queue
     *
     * @param  string|Zend_Queue_Adapter_AdapterInterface $adapter
     * @return Zend_Queue Provides a fluent interface
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            if (null === ($adapterNamespace = $this->getOption('adapterNamespace'))) {
                $adapterNamespace = 'Zend_Queue_Adapter';
            }

            $adapterName = str_replace(
                ' ',
                '_',
                ucwords(
                    str_replace(
                        '_',
                        ' ',
                        strtolower($adapterNamespace . '_' . $adapter)
                    )
                )
            );

            if (!class_exists($adapterName)) {
                #require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($adapterName);
            }

            /*
             * Create an instance of the adapter class.
             * Pass the configuration to the adapter class constructor.
             */
            $adapter = new $adapterName($this->getOptions(), $this);
        }

        if (!$adapter instanceof Zend_Queue_Adapter_AdapterInterface) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Adapter class '" . get_class($adapterName) . "' does not implement Zend_Queue_Adapter_AdapterInterface");
        }

        $this->_adapter = $adapter;

        $this->_adapter->setQueue($this);

        if (null !== ($name = $this->getOption(self::NAME))) {
            $this->_setName($name);
        }

        return $this;
    }

    /**
     * Get the adapter for this queue
     *
     * @return Zend_Queue_Adapter_AdapterInterface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @param  string $className
     * @return Zend_Queue Provides a fluent interface
     */
    public function setMessageClass($className)
    {
        $this->_messageClass = (string) $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->_messageClass;
    }

    /**
     * @param  string $className
     * @return Zend_Queue Provides a fluent interface
     */
    public function setMessageSetClass($className)
    {
        $this->_messageSetClass = (string) $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageSetClass()
    {
        return $this->_messageSetClass;
    }

    /**
     * Get the name of the queue
     *
     * Note: _setName() used to exist, but it caused confusion with createQueue
     * Will evaluate later to see if we should add it back in.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getOption(self::NAME);
    }

    /**
     * Create a new queue
     *
     * @param  string           $name    queue name
     * @param  integer          $timeout default visibility timeout
     * @return Zend_Queue|false
     * @throws Zend_Queue_Exception
     */
    public function createQueue($name, $timeout = null)
    {
        if (!is_string($name)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$name is not a string');
        }

        if ((null !== $timeout) && !is_integer($timeout)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$timeout must be an integer');
        }

        // Default to standard timeout
        if (null === $timeout) {
            $timeout = $this->getOption(self::TIMEOUT);
        }

        // Some queues allow you to create on the fly, but cannot return
        // a list of queues.  Stomp protocol for example.
        if ($this->isSupported('create')) {
            if ($this->getAdapter()->isExists($name)) {
                return false;
            }

            if (!$this->getAdapter()->create($name, $timeout)) {
                return false;
            }
        }

        $options = array(
            self::NAME  => $name,
            'timeout'   => $timeout
        );

        return new self($this->getAdapter(), $options);
    }

    /**
     * Delete the queue this object is working on.
     *
     * This queue is disabled, regardless of the outcome of the deletion
     * of the queue, because the programmers intent is to disable this queue.
     *
     * @return boolean
     */
    public function deleteQueue()
    {
        if ($this->isSupported('delete')) {
            $deleted = $this->getAdapter()->delete($this->getName());
        }
        else {
            $deleted = true;
        }

        /**
         * @see Zend_Queue_Adapter_Null
         */
        #require_once('Zend/Queue/Adapter/Null.php');
        $this->setAdapter(new Zend_Queue_Adapter_Null($this->getOptions()));

        return $deleted;
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * Returns true if the adapter doesn't support message deletion.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function deleteMessage(Zend_Queue_Message $message)
    {
        if ($this->getAdapter()->isSupported('deleteMessage')) {
            return $this->getAdapter()->deleteMessage($message);
        }
        return true;
    }

    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message)
    {
        return $this->getAdapter()->send($message);
    }

    /**
     * Returns the approximate number of messages in the queue
     *
     * @return integer
     */
    public function count()
    {
        if ($this->getAdapter()->isSupported('count')) {
            return $this->getAdapter()->count();
        }
        return 0;
    }

    /**
     * Return the first element in the queue
     *
     * @param  integer $maxMessages
     * @param  integer $timeout
     * @return Zend_Queue_Message_Iterator
     */
    public function receive($maxMessages=null, $timeout=null)
    {
        if (($maxMessages !== null) && !is_integer($maxMessages)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$maxMessages must be an integer or null');
        }

        if (($timeout !== null) && !is_integer($timeout)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('$timeout must be an integer or null');
        }

        // Default to returning only one message
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        // Default to standard timeout
        if ($timeout === null) {
            $timeout = $this->getOption(self::TIMEOUT);
        }

        return $this->getAdapter()->receive($maxMessages, $timeout);
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
        return $this->getAdapter()->getCapabilities();
    }

    /**
     * Indicates if a function is supported or not.
     *
     * @param  string $name
     * @return boolean
     */
    public function isSupported($name)
    {
        $translation = array(
            'deleteQueue' => 'delete',
            'createQueue' => 'create'
        );

        if (isset($translation[$name])) {
            $name = $translation[$name];
        }

        return $this->getAdapter()->isSupported($name);
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws Zend_Queue_Exception
     */
    public function getQueues()
    {
        if (!$this->isSupported('getQueues')) {
            throw new Zend_Queue_Exception( __FUNCTION__ . '() is not supported by ' . get_class($this->getAdapter()));
        }

        return $this->getAdapter()->getQueues();
    }

    /**
     * Set the name of the queue
     *
     * This is AN UNSUPPORTED FUNCTION
     *
     * @param  string           $name
     * @return Zend_Queue|false Provides a fluent interface
     */
    protected function _setName($name)
    {
        if (!is_string($name)) {
            /**
             * @see Zend_Queue_Exception
             */
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("$name is not a string");
        }

        if ($this->getAdapter()->isSupported('create')) {
            if (!$this->getAdapter()->isExists($name)) {
                $timeout = $this->getOption(self::TIMEOUT);

                if (!$this->getAdapter()->create($name, $timeout)) {
                    // Unable to create the new queue
                    return false;
                }
            }
        }

        $this->setOption(self::NAME, $name);

        return $this;
    }

    /**
     * returns a listing of Zend_Queue details.
     * useful for debugging
     *
     * @return array
     */
    public function debugInfo()
    {
        $info = array();
        $info['self']                     = get_class($this);
        $info['adapter']                  = get_class($this->getAdapter());
        foreach ($this->getAdapter()->getCapabilities() as $feature => $supported) {
            $info['adapter-' . $feature]  = ($supported) ? 'yes' : 'no';
        }
        $info['options']                  = $this->getOptions();
        $info['options']['driverOptions'] = '[hidden]';
        $info['currentQueue']             = $this->getName();
        $info['messageClass']             = $this->getMessageClass();
        $info['messageSetClass']          = $this->getMessageSetClass();

        return $info;
    }
}
