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
 * @subpackage Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SessionHandler.php 20785 2010-01-31 09:43:03Z mikaelkael $
 */

/** Zend_Service_WindowsAzure_Storage_Table */
#require_once 'Zend/Service/WindowsAzure/Storage/Table.php';

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
#require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_SessionHandler
{
    /**
     * Table storage
     * 
     * @var Zend_Service_WindowsAzure_Storage_Table
     */
    protected $_tableStorage;
    
    /**
     * Session table name
     * 
     * @var string
     */
    protected $_sessionTable;
    
    /**
     * Session table partition
     * 
     * @var string
     */
    protected $_sessionTablePartition;
	
    /**
     * Creates a new Zend_Service_WindowsAzure_SessionHandler instance
     * 
     * @param Zend_Service_WindowsAzure_Storage_Table $tableStorage Table storage
     * @param string $sessionTable Session table name
     * @param string $sessionTablePartition Session table partition
     */
    public function __construct(Zend_Service_WindowsAzure_Storage_Table $tableStorage, $sessionTable = 'phpsessions', $sessionTablePartition = 'sessions')
	{
	    // Set properties
		$this->_tableStorage = $tableStorage;
		$this->_sessionTable = $sessionTable;
		$this->_sessionTablePartition = $sessionTablePartition;
	}
	
	/**
	 * Registers the current session handler as PHP's session handler
	 * 
	 * @return boolean
	 */
	public function register()
	{
        return session_set_save_handler(array($this, 'open'),
                                        array($this, 'close'),
                                        array($this, 'read'),
                                        array($this, 'write'),
                                        array($this, 'destroy'),
                                        array($this, 'gc')
        );
	}
	
    /**
     * Open the session store
     * 
     * @return bool
     */
    public function open()
    {
    	// Make sure table exists
    	$tableExists = $this->_tableStorage->tableExists($this->_sessionTable);
    	if (!$tableExists) {
		    $this->_tableStorage->createTable($this->_sessionTable);
		}
		
		// Ok!
		return true;
    }

    /**
     * Close the session store
     * 
     * @return bool
     */
    public function close()
    {
        return true;
    }
    
    /**
     * Read a specific session
     * 
     * @param int $id Session Id
     * @return string
     */
    public function read($id)
    {
        try
        {
            $sessionRecord = $this->_tableStorage->retrieveEntityById(
                $this->_sessionTable,
                $this->_sessionTablePartition,
                $id
            );
            return base64_decode($sessionRecord->serializedData);
        }
        catch (Zend_Service_WindowsAzure_Exception $ex)
        {
            return '';
        }
    }
    
    /**
     * Write a specific session
     * 
     * @param int $id Session Id
     * @param string $serializedData Serialized PHP object
     */
    public function write($id, $serializedData)
    {
        $sessionRecord = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($this->_sessionTablePartition, $id);
        $sessionRecord->sessionExpires = time();
        $sessionRecord->serializedData = base64_encode($serializedData);
        
        $sessionRecord->setAzurePropertyType('sessionExpires', 'Edm.Int32');

        try
        {
            $this->_tableStorage->updateEntity($this->_sessionTable, $sessionRecord);
        }
        catch (Zend_Service_WindowsAzure_Exception $unknownRecord)
        {
            $this->_tableStorage->insertEntity($this->_sessionTable, $sessionRecord);
        }
    }
    
    /**
     * Destroy a specific session
     * 
     * @param int $id Session Id
     * @return boolean
     */
    public function destroy($id)
    {
        try
        {
            $sessionRecord = $this->_tableStorage->retrieveEntityById(
                $this->_sessionTable,
                $this->_sessionTablePartition,
                $id
            );
            $this->_tableStorage->deleteEntity($this->_sessionTable, $sessionRecord);
            
            return true;
        }
        catch (Zend_Service_WindowsAzure_Exception $ex)
        {
            return false;
        }
    }
    
    /**
     * Garbage collector
     * 
     * @param int $lifeTime Session maximal lifetime
     * @see session.gc_divisor  100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability 1
     * @usage Execution rate 1/100 (session.gc_probability/session.gc_divisor)
     * @return boolean
     */
    public function gc($lifeTime)
    {
        try
        {
            $result = $this->_tableStorage->retrieveEntities($this->_sessionTable, 'PartitionKey eq \'' . $this->_sessionTablePartition . '\' and sessionExpires lt ' . (time() - $lifeTime));
            foreach ($result as $sessionRecord)
            {
                $this->_tableStorage->deleteEntity($this->_sessionTable, $sessionRecord);
            }
            return true;
        }
        catch (Zend_Service_WindowsAzure_exception $ex)
        {
            return false;
        }
    }
}
