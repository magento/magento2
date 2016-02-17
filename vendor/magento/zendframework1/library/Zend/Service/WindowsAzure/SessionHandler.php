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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Session
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_SessionHandler
{
	/**
	 * Maximal property size in table storage.
	 *
	 * @var int
	 * @see http://msdn.microsoft.com/en-us/library/dd179338.aspx
	 */
	const MAX_TS_PROPERTY_SIZE = 65536;

	/** Storage backend type */
	const STORAGE_TYPE_TABLE = 'table';
	const STORAGE_TYPE_BLOB = 'blob';

    /**
     * Storage back-end
     *
     * @var Zend_Service_WindowsAzure_Storage_Table|Zend_Service_WindowsAzure_Storage_Blob
     */
    protected $_storage;

    /**
     * Storage backend type
     *
     * @var string
     */
    protected $_storageType;

    /**
     * Session container name
     *
     * @var string
     */
    protected $_sessionContainer;

    /**
     * Session container partition
     *
     * @var string
     */
    protected $_sessionContainerPartition;

    /**
     * Creates a new Zend_Service_WindowsAzure_SessionHandler instance
     *
     * @param Zend_Service_WindowsAzure_Storage_Table|Zend_Service_WindowsAzure_Storage_Blob $storage Storage back-end, can be table storage and blob storage
     * @param string $sessionContainer Session container name
     * @param string $sessionContainerPartition Session container partition
     */
    public function __construct(Zend_Service_WindowsAzure_Storage $storage, $sessionContainer = 'phpsessions', $sessionContainerPartition = 'sessions')
	{
		// Validate $storage
		if (!($storage instanceof Zend_Service_WindowsAzure_Storage_Table || $storage instanceof Zend_Service_WindowsAzure_Storage_Blob)) {
			#require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Invalid storage back-end given. Storage back-end should be of type Zend_Service_WindowsAzure_Storage_Table or Zend_Service_WindowsAzure_Storage_Blob.');
		}

		// Validate other parameters
		if ($sessionContainer == '' || $sessionContainerPartition == '') {
			#require_once 'Zend/Service/WindowsAzure/Exception.php';
			throw new Zend_Service_WindowsAzure_Exception('Session container and session partition should be specified.');
		}

		// Determine storage type
		$storageType = self::STORAGE_TYPE_TABLE;
		if ($storage instanceof Zend_Service_WindowsAzure_Storage_Blob) {
			$storageType = self::STORAGE_TYPE_BLOB;
		}

	    // Set properties
		$this->_storage = $storage;
		$this->_storageType = $storageType;
		$this->_sessionContainer = $sessionContainer;
		$this->_sessionContainerPartition = $sessionContainerPartition;
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
    	// Make sure storage container exists
    	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		$this->_storage->createTableIfNotExists($this->_sessionContainer);
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		$this->_storage->createContainerIfNotExists($this->_sessionContainer);
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
    	// Read data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
	        try
	        {
	            $sessionRecord = $this->_storage->retrieveEntityById(
	                $this->_sessionContainer,
	                $this->_sessionContainerPartition,
	                $id
	            );
	            return unserialize(base64_decode($sessionRecord->serializedData));
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return '';
	        }
       	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
    			$data = $this->_storage->getBlobData(
    				$this->_sessionContainer,
    				$this->_sessionContainerPartition . '/' . $id
    			);
	            return unserialize(base64_decode($data));
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
    	}
    }

    /**
     * Write a specific session
     *
     * @param int $id Session Id
     * @param string $serializedData Serialized PHP object
     * @throws Exception
     */
    public function write($id, $serializedData)
    {
    	// Encode data
    	$serializedData = base64_encode(serialize($serializedData));
    	if (strlen($serializedData) >= self::MAX_TS_PROPERTY_SIZE && $this->_storageType == self::STORAGE_TYPE_TABLE) {
    		throw new Zend_Service_WindowsAzure_Exception('Session data exceeds the maximum allowed size of ' . self::MAX_TS_PROPERTY_SIZE . ' bytes that can be stored using table storage. Consider switching to a blob storage back-end or try reducing session data size.');
    	}

    	// Store data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    $sessionRecord = new Zend_Service_WindowsAzure_Storage_DynamicTableEntity($this->_sessionContainerPartition, $id);
	        $sessionRecord->sessionExpires = time();
	        $sessionRecord->serializedData = $serializedData;

	        $sessionRecord->setAzurePropertyType('sessionExpires', 'Edm.Int32');

	        try
	        {
	            $this->_storage->updateEntity($this->_sessionContainer, $sessionRecord);
	        }
	        catch (Zend_Service_WindowsAzure_Exception $unknownRecord)
	        {
	            $this->_storage->insertEntity($this->_sessionContainer, $sessionRecord);
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    		$this->_storage->putBlobData(
    			$this->_sessionContainer,
    			$this->_sessionContainerPartition . '/' . $id,
    			$serializedData,
    			array('sessionexpires' => time())
    		);
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
		// Destroy data
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    try
	        {
	            $sessionRecord = $this->_storage->retrieveEntityById(
	                $this->_sessionContainer,
	                $this->_sessionContainerPartition,
	                $id
	            );
	            $this->_storage->deleteEntity($this->_sessionContainer, $sessionRecord);

	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
    			$this->_storage->deleteBlob(
    				$this->_sessionContainer,
    				$this->_sessionContainerPartition . '/' . $id
    			);

	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_Exception $ex)
	        {
	            return false;
	        }
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
       	if ($this->_storageType == self::STORAGE_TYPE_TABLE) {
    		// In table storage
       	    try
	        {
	            $result = $this->_storage->retrieveEntities($this->_sessionContainer, 'PartitionKey eq \'' . $this->_sessionContainerPartition . '\' and sessionExpires lt ' . (time() - $lifeTime));
	            foreach ($result as $sessionRecord)
	            {
	                $this->_storage->deleteEntity($this->_sessionContainer, $sessionRecord);
	            }
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_exception $ex)
	        {
	            return false;
	        }
    	} else if ($this->_storageType == self::STORAGE_TYPE_BLOB) {
    		// In blob storage
    	    try
	        {
	            $result = $this->_storage->listBlobs($this->_sessionContainer, $this->_sessionContainerPartition, '', null, null, 'metadata');
	            foreach ($result as $sessionRecord)
	            {
	            	if ($sessionRecord->Metadata['sessionexpires'] < (time() - $lifeTime)) {
	                	$this->_storage->deleteBlob($this->_sessionContainer, $sessionRecord->Name);
	            	}
	            }
	            return true;
	        }
	        catch (Zend_Service_WindowsAzure_exception $ex)
	        {
	            return false;
	        }
    	}
    }
}
