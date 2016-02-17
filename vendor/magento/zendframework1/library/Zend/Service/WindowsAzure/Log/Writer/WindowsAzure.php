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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Log_Writer_Abstract
 */
#require_once 'Zend/Log/Writer/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Log
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Log_Writer_WindowsAzure
    extends Zend_Log_Writer_Abstract
{
    /**
     * @var Zend_Service_Log_Formatter_Interface
     */
    protected $_formatter;

    /**
     * Connection to a windows Azure
     *
     * @var Zend_Service_Service_WindowsAzure_Storage_Table
     */
    protected $_tableStorageConnection = null;

    /**
     * Name of the table to use for logging purposes
     *
     * @var string
     */
    protected $_tableName = null;

    /**
     * Whether to keep all messages to be logged in an external buffer until the script ends and
     * only then to send the messages in batch to the logging component.
     *
     * @var bool
     */
    protected $_bufferMessages = false;

    /**
     * If message buffering is activated, it will store all the messages in this buffer and only
     * write them to table storage (in a batch transaction) when the script ends.
     *
     * @var array
     */
    protected $_messageBuffer = array();

    /**
     * @param Zend_Service_Service_WindowsAzure_Storage_Table|Zend_Service_WindowsAzure_Storage_Table $tableStorageConnection
     * @param string                                                                                  $tableName
     * @param bool                                                                                    $createTable create the Windows Azure table for logging if it does not exist
     * @param bool                                                                                    $bufferMessages
     * @throws Zend_Service_Log_Exception
     */
    public function __construct(
        Zend_Service_WindowsAzure_Storage_Table $tableStorageConnection,
        $tableName, $createTable = true, $bufferMessages = true
    )
    {
        if ($tableStorageConnection == null) {
            #require_once 'Zend/Service/Log/Exception.php';
            throw new Zend_Service_Log_Exception(
                'No connection to the Windows Azure tables provided.'
            );
        }

        if (!is_string($tableName)) {
            #require_once 'Zend/Service/Log/Exception.php';
            throw new Zend_Service_Log_Exception(
                'Provided Windows Azure table name must be a string.'
            );
        }

        $this->_tableStorageConnection = $tableStorageConnection;
        $this->_tableName              = $tableName;

        // create the logging table if it does not exist. It will add some overhead, so it's optional
        if ($createTable) {
            $this->_tableStorageConnection->createTableIfNotExists(
                $this->_tableName
            );
        }

        // keep messages to be logged in an internal buffer and only send them over the wire when
        // the script execution ends
        if ($bufferMessages) {
            $this->_bufferMessages = $bufferMessages;
        }

        $this->_formatter =
            new Zend_Service_WindowsAzure_Log_Formatter_WindowsAzure();
    }

    /**
     * If the log messages have been stored in the internal buffer, just send them
     * to table storage.
     */
    public function shutdown()
    {
        parent::shutdown();
        if ($this->_bufferMessages) {
            $this->_tableStorageConnection->startBatch();
            foreach ($this->_messageBuffer as $logEntity) {
                $this->_tableStorageConnection->insertEntity(
                    $this->_tableName, $logEntity
                );
            }
            $this->_tableStorageConnection->commit();
        }
    }

    /**
     * Create a new instance of Zend_Service_Log_Writer_WindowsAzure
     *
     * @param  array $config
     * @return Zend_Service_Log_Writer_WindowsAzure
     * @throws Zend_Service_Log_Exception
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(
            array(
                 'connection' => null,
                 'tableName' => null,
                 'createTable' => true,
            ), $config
        );

        return new self(
            $config['connection'],
            $config['tableName'],
            $config['createTable']
        );
    }

    /**
     * The only formatter accepted is already  loaded in the constructor
     *
     * @todo enable custom formatters using the WindowsAzure_Storage_DynamicTableEntity class
     */
    public function setFormatter(
        Zend_Service_Log_Formatter_Interface $formatter
    )
    {
        #require_once 'Zend/Service/Log/Exception.php';
        throw new Zend_Service_Log_Exception(
            get_class($this) . ' does not support formatting');
    }

    /**
     * Write a message to the table storage. If buffering is activated, then messages will just be
     * added to an internal buffer.
     *
     * @param  array $event
     * @return void
     * @todo   format the event using a formatted, not in this method
     */
    protected function _write($event)
    {
        $logEntity = $this->_formatter->format($event);

        if ($this->_bufferMessages) {
            $this->_messageBuffer[] = $logEntity;
        } else {
            $this->_tableStorageConnection->insertEntity(
                $this->_tableName, $logEntity
            );
        }
    }
}
