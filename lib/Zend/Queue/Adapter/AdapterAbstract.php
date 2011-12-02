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
 * @version    $Id: AdapterAbstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Queue
 */
#require_once 'Zend/Queue.php';

/**
 * @see Zend_Queue_Adapter_AdapterInterface
 */
#require_once 'Zend/Queue/Adapter/AdapterInterface.php';

/**
 * Class for connecting to queues performing common operations.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Queue_Adapter_AdapterAbstract
    implements Zend_Queue_Adapter_AdapterInterface
{
    /**
     * Default timeout for createQueue() function
     */
    const CREATE_TIMEOUT_DEFAULT = 30;

    /**
     * Default timeout for recieve() function
     */
    const RECEIVE_TIMEOUT_DEFAULT = 30;

    /**
     * User-provided options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Internal array of queues to save on lookups
     *
     * @var array
     */
    protected $_queues = array();

    /**
     * Contains the Zend_Queue that this object
     *
     * @var Zend_Queue_Adapter_Abstract
     */
    protected $_queue = null;

    /**
     * Constructor.
     *
     * $options is an array of key/value pairs or an instance of Zend_Config
     * containing configuration options.  These options are common to most adapters:
     *
     * See the Zend_Queue Adapter Notes documentation for example configurations.
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * access_key     => (string) Amazon AWS Access Key
     * secret_key     => (string) Amazon AWS Secret Key
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     * port           => (string) The port of the database
     *
     * @param  array|Zend_Config $config An array having configuration data
     * @param  Zend_Queue The Zend_Queue object that created this class
     * @return void
     * @throws Zend_Queue_Exception
     */
    public function __construct($options, Zend_Queue $queue = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($options)) {
            #require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Adapter options must be an array or Zend_Config object');
        }

        // set the queue
        if ($queue !== null) {
            $this->setQueue($queue);
        }

        $adapterOptions = array();
        $driverOptions  = array();

        // Normalize the options and merge with the defaults
        if (array_key_exists('options', $options)) {
            if (!is_array($options['options'])) {
                #require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception("Configuration array 'options' must be an array");
            }

            // Can't use array_merge() because keys might be integers
            foreach ($options['options'] as $key => $value) {
                $adapterOptions[$key] = $value;
            }
        }
        if (array_key_exists('driverOptions', $options)) {
            // can't use array_merge() because keys might be integers
            foreach ((array)$options['driverOptions'] as $key => $value) {
                $driverOptions[$key] = $value;
            }
        }
        $this->_options = array_merge($this->_options, $options);
        $this->_options['options']       = $adapterOptions;
        $this->_options['driverOptions'] = $driverOptions;
    }

    /********************************************************************
    * Queue management functions
     *********************************************************************/
    /**
     * get the Zend_Queue class that is attached to this object
     *
     * @return Zend_Queue|null
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * set the Zend_Queue class for this object
     *
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Adapter_AdapterInterface
     */
    public function setQueue(Zend_Queue $queue)
    {
        $this->_queue = $queue;
        return $this;
    }

    /**
     * Returns the configuration options in this adapter.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Indicates if a function is supported or not.
     *
     * @param  string $name
     * @return boolean
     */
    public function isSupported($name)
    {
        $list = $this->getCapabilities();

        return (isset($list[$name]) && $list[$name]);
     }
}
