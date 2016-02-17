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
 * @package    Zend_Cloud_StorageService
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/StorageService/Adapter.php';
#require_once 'Zend/Cloud/StorageService/Exception.php';
#require_once 'Zend/Service/Rackspace/Files.php';
#require_once 'Zend/Service/Rackspace/Exception.php';

/**
 * Adapter for Rackspace cloud storage
 *
 * @category   Zend
 * @package    Zend_Cloud_StorageService
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_Rackspace
    implements Zend_Cloud_StorageService_Adapter
{
    const USER                = 'user';
    const API_KEY             = 'key';
    const REMOTE_CONTAINER    = 'container';
    const DELETE_METADATA_KEY = 'ZF_metadata_deleted';

    /**
     * The Rackspace adapter
     * @var Zend_Service_Rackspace_Files
     */
    protected $_rackspace;

    /**
     * Container in which files are stored
     * @var string
     */
    protected $_container = 'default';

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     * @return void
     */
    function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options) || empty($options)) {
            throw new Zend_Cloud_StorageService_Exception('Invalid options provided');
        }

        try {
            $this->_rackspace = new Zend_Service_Rackspace_Files($options[self::USER], $options[self::API_KEY]);
        } catch (Zend_Service_Rackspace_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on create: '.$e->getMessage(), $e->getCode(), $e);
        }

        if (isset($options[self::HTTP_ADAPTER])) {
            $this->_rackspace->getHttpClient()->setAdapter($options[self::HTTP_ADAPTER]);
        }
        if (!empty($options[self::REMOTE_CONTAINER])) {
            $this->_container = $options[self::REMOTE_CONTAINER];
        }
    }

     /**
     * Get an item from the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return mixed
     */
    public function fetchItem($path, $options = null)
    {
        $item = $this->_rackspace->getObject($this->_container,$path, $options);
        if (!$this->_rackspace->isSuccessful() && ($this->_rackspace->getErrorCode()!='404')) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$this->_rackspace->getErrorMsg());
        }
        if (!empty($item)) {
            return $item->getContent();
        } else {
            return false;
        }
    }

    /**
     * Store an item in the storage service.
     *
     * @param  string $destinationPath
     * @param  mixed $data
     * @param  array $options
     * @return void
     */
    public function storeItem($destinationPath, $data, $options = null)
    {
        $this->_rackspace->storeObject($this->_container,$destinationPath,$data,$options);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on store: '.$this->_rackspace->getErrorMsg());
        }
    }

    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteItem($path, $options = null)
    {
        $this->_rackspace->deleteObject($this->_container,$path);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on delete: '.$this->_rackspace->getErrorMsg());
        }
    }

    /**
     * Copy an item in the storage service to a given path.
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = null)
    {
        $this->_rackspace->copyObject($this->_container,$sourcePath,$this->_container,$destinationPath,$options);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on copy: '.$this->_rackspace->getErrorMsg());
        }
    }

    /**
     * Move an item in the storage service to a given path.
     * WARNING: This operation is *very* expensive for services that do not
     * support moving an item natively.
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function moveItem($sourcePath, $destinationPath, $options = null)
    {
        try {
            $this->copyItem($sourcePath, $destinationPath, $options);
        } catch (Zend_Service_Rackspace_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on move: '.$e->getMessage());
        }
        try {
            $this->deleteItem($sourcePath);
        } catch (Zend_Service_Rackspace_Exception $e) {
            $this->deleteItem($destinationPath);
            throw new Zend_Cloud_StorageService_Exception('Error on move: '.$e->getMessage());
        }
    }

    /**
     * Rename an item in the storage service to a given name.
     *
     * @param  string $path
     * @param  string $name
     * @param  array $options
     * @return void
     */
    public function renameItem($path, $name, $options = null)
    {
        #require_once 'Zend/Cloud/OperationNotAvailableException.php';
        throw new Zend_Cloud_OperationNotAvailableException('Renaming not implemented');
    }

    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return array An associative array of key/value pairs specifying the metadata for this object.
     *                  If no metadata exists, an empty array is returned.
     */
    public function fetchMetadata($path, $options = null)
    {
        $result = $this->_rackspace->getMetadataObject($this->_container,$path);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetch metadata: '.$this->_rackspace->getErrorMsg());
        }
        $metadata = array();
        if (isset($result['metadata'])) {
            $metadata =  $result['metadata'];
        }
        // delete the self::DELETE_METADATA_KEY - this is a trick to remove all
        // the metadata information of an object (see deleteMetadata).
        // Rackspace doesn't have an API to remove the metadata of an object
        unset($metadata[self::DELETE_METADATA_KEY]);
        return $metadata;
    }

    /**
     * Store a key/value array of metadata at the given path.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath.
     *
     * @param  string $destinationPath
     * @param  array  $metadata        associative array specifying the key/value pairs for the metadata.
     * @param  array  $options
     * @return void
     */
    public function storeMetadata($destinationPath, $metadata, $options = null)
    {
        $this->_rackspace->setMetadataObject($this->_container, $destinationPath, $metadata);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on store metadata: '.$this->_rackspace->getErrorMsg());
        }
     }

    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param  string $path
     * @param  array $metadata - An associative array specifying the key/value pairs for the metadata
     *                           to be deleted.  If null, all metadata associated with the object will
     *                           be deleted.
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path, $metadata = null, $options = null)
    {
        if (empty($metadata)) {
            $newMetadata = array(self::DELETE_METADATA_KEY => true);
            try {
                $this->storeMetadata($path, $newMetadata);
            } catch (Zend_Service_Rackspace_Exception $e) {
                throw new Zend_Cloud_StorageService_Exception('Error on delete metadata: '.$e->getMessage());
            }
        } else {
            try {
                $oldMetadata = $this->fetchMetadata($path);
            } catch (Zend_Service_Rackspace_Exception $e) {
                throw new Zend_Cloud_StorageService_Exception('Error on delete metadata: '.$e->getMessage());
            }
            $newMetadata = array_diff_assoc($oldMetadata, $metadata);
            try {
                $this->storeMetadata($path, $newMetadata);
            } catch (Zend_Service_Rackspace_Exception $e) {
                throw new Zend_Cloud_StorageService_Exception('Error on delete metadata: '.$e->getMessage());
            }
        }
    }

    /*
     * Recursively traverse all the folders and build an array that contains
     * the path names for each folder.
     *
     * @param  string $path        folder path to get the list of folders from.
     * @param  array& $resultArray reference to the array that contains the path names
     *                             for each folder.
     * @return void
     */
    private function getAllFolders($path, &$resultArray)
    {
        if (!empty($path)) {
            $options = array (
                'prefix'    => $path
            );
        }
        $files = $this->_rackspace->getObjects($this->_container,$options);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on get all folders: '.$this->_rackspace->getErrorMsg());
        }
        $resultArray = array();
        foreach ($files as $file) {
            $resultArray[dirname($file->getName())] = true;
        }
        $resultArray = array_keys($resultArray);
    }

    /**
     * Return an array of the items contained in the given path.  The items
     * returned are the files or objects that in the specified path.
     *
     * @param  string $path
     * @param  array  $options
     * @return array
     */
    public function listItems($path, $options = null)
    {
        if (!empty($path)) {
            $options = array (
                'prefix'    => $path
            );
        }

        $files = $this->_rackspace->getObjects($this->_container,$options);
        if (!$this->_rackspace->isSuccessful()) {
            throw new Zend_Cloud_StorageService_Exception('Error on list items: '.$this->_rackspace->getErrorMsg());
        }
        $resultArray = array();
        if (!empty($files)) {
            foreach ($files as $file) {
                $resultArray[] = $file->getName();
            }
        }
        return $resultArray;
    }

    /**
     * Get the concrete client.
     *
     * @return Zend_Service_Rackspace_File
     */
    public function getClient()
    {
         return $this->_rackspace;
    }
}
