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
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/StorageService/Adapter.php';
#require_once 'Zend/Service/WindowsAzure/Storage/Blob.php';
#require_once 'Zend/Cloud/StorageService/Exception.php';

/**
 *
 * Windows Azure Blob Service abstraction
 * 
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_WindowsAzure 
    implements Zend_Cloud_StorageService_Adapter
{
    const ACCOUNT_NAME      = 'storage_accountname';
    const ACCOUNT_KEY       = 'storage_accountkey';
    const HOST              = "storage_host";
    const PROXY_HOST        = "storage_proxy_host";
    const PROXY_PORT        = "storage_proxy_port";
    const PROXY_CREDENTIALS = "storage_proxy_credentials";
    const CONTAINER         = "storage_container";
    const RETURN_TYPE       = 'return_type';
    const RETURN_PATHNAME   = 'return_path';
    const RETURN_OPENMODE   = 'return_openmode';
    
    /** return types  for fetch */
    const RETURN_PATH   = 1;   // return filename
    const RETURN_STRING = 2; // return data as string
    const RETURN_STREAM = 3; // return PHP stream

    /** return types  for list */
    const RETURN_LIST  = 1;   // return native list
    const RETURN_NAMES = 2;  // return only names
    
    const DEFAULT_HOST = Zend_Service_WindowsAzure_Storage::URL_CLOUD_BLOB;

    /**
     * Storage container to operate on
     * 
     * @var string
     */
    protected $_container;
    
    /**
     * Storage client
     * 
     * @var Zend_Service_WindowsAzure_Storage_Blob
     */
    protected $_storageClient = null;

    /**
     * Creates a new Zend_Cloud_Storage_WindowsAzure instance
     * 
     * @param array|Zend_Config  $options   Options for the Zend_Cloud_Storage_WindowsAzure instance
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_StorageService_Exception('Invalid options provided');
        }

        // Build Zend_Service_WindowsAzure_Storage_Blob instance
        if (!isset($options[self::HOST])) {
            $host = self::DEFAULT_HOST;
        } else {
            $host = $options[self::HOST];
        }
        
        if (!isset($options[self::ACCOUNT_NAME])) {
            throw new Zend_Cloud_StorageService_Exception('No Windows Azure account name provided.');
        }
        if (!isset($options[self::ACCOUNT_KEY])) {
            throw new Zend_Cloud_StorageService_Exception('No Windows Azure account key provided.');
        }
            
        $this->_storageClient = new Zend_Service_WindowsAzure_Storage_Blob($host,
             $options[self::ACCOUNT_NAME], $options[self::ACCOUNT_KEY]);
        
        // Parse other options
        if (!empty($options[self::PROXY_HOST])) {
            $proxyHost = $options[self::PROXY_HOST];
            $proxyPort = isset($options[self::PROXY_PORT]) ? $options[self::PROXY_PORT] : 8080;
            $proxyCredentials = isset($options[self::PROXY_CREDENTIALS]) ? $options[self::PROXY_CREDENTIALS] : '';
            
            $this->_storageClient->setProxy(true, $proxyHost, $proxyPort, $proxyCredentials);
        }
        
        if (isset($options[self::HTTP_ADAPTER])) {
            $this->_storageClient->setHttpClientChannel($options[self::HTTP_ADAPTER]);
        }
        
        // Set container
        $this->_container = $options[self::CONTAINER];

        // Make sure the container exists
        if (!$this->_storageClient->containerExists($this->_container)) {
            $this->_storageClient->createContainer($this->_container);
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
        // Options
        $returnType = self::RETURN_STRING; 
        $returnPath = tempnam('', 'azr');
        $openMode   = 'r';
        
        // Parse options
        if (is_array($options)) {
            if (isset($options[self::RETURN_TYPE])) {
                $returnType = $options[self::RETURN_TYPE];
            }
                
            if (isset($options[self::RETURN_PATHNAME])) {
                $returnPath = $options[self::RETURN_PATHNAME];
            }
                
            if (isset($options[self::RETURN_OPENMODE])) {
                $openMode = $options[self::RETURN_OPENMODE];
            }
        }
        
        // Fetch the blob
        try {
            $this->_storageClient->getBlob(
                $this->_container,
                $path,
                $returnPath
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "does not exist") !== false) {
                return false;
            }
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
        
        // Return value
        if ($returnType == self::RETURN_PATH) {
            return $returnPath;
        }
        if ($returnType == self::RETURN_STRING) {
            return file_get_contents($returnPath);
        }
        if ($returnType == self::RETURN_STREAM) {
            return fopen($returnPath, $openMode);
        }
    }
    
    /**
     * Store an item in the storage service.
     * WARNING: This operation overwrites any item that is located at 
     * $destinationPath.
     * @param string $destinationPath
     * @param mixed  $data
     * @param  array $options
     * @return boolean
     */
    public function storeItem($destinationPath, $data, $options = null) 
    {
        // Create a temporary file that will be uploaded
        $temporaryFilePath       = '';
        $removeTemporaryFilePath = false;

        if (is_resource($data))    {
            $temporaryFilePath = tempnam('', 'azr');
            $fpDestination     = fopen($temporaryFilePath, 'w');

            $fpSource = $data;
            rewind($fpSource);
            while (!feof($fpSource)) {
                fwrite($fpDestination, fread($fpSource, 8192));
            }
            
            fclose($fpDestination);
            
            $removeTemporaryFilePath = true;
        } elseif (file_exists($data)) {
            $temporaryFilePath       = $data;
            $removeTemporaryFilePath = false;
        } else {
            $temporaryFilePath = tempnam('', 'azr');
            file_put_contents($temporaryFilePath, $data);
            $removeTemporaryFilePath = true;
        }
        
        try {
            // Upload data
            $this->_storageClient->putBlob(
                $this->_container,
                $destinationPath,
                $temporaryFilePath
            );
        } catch(Zend_Service_WindowsAzure_Exception $e) {
            @unlink($temporaryFilePath);
            throw new Zend_Cloud_StorageService_Exception('Error on store: '.$e->getMessage(), $e->getCode(), $e);        
        }
        if ($removeTemporaryFilePath) {
            @unlink($temporaryFilePath);
        }
    }
    
    /**
     * Delete an item in the storage service.
     *
     * @param  string $path
     * @param  array  $options
     * @return void
     */
    public function deleteItem($path, $options = null)
    {
        try {
            $this->_storageClient->deleteBlob(
                $this->_container,
                $path
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on delete: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Copy an item in the storage service to a given path.
     *
     * @param  string $sourcePath
     * @param  string $destinationPath
     * @param  array  $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = null)
    {
        try {
            $this->_storageClient->copyBlob(
                $this->_container,
                $sourcePath,
                $this->_container,
                $destinationPath
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on copy: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Move an item in the storage service to a given path.
     *
     * @param  string $sourcePath
     * @param  string $destinationPath
     * @param  array  $options
     * @return void
     */
    public function moveItem($sourcePath, $destinationPath, $options = null)
    {
        try {
            $this->_storageClient->copyBlob(
                $this->_container,
                $sourcePath,
                $this->_container,
                $destinationPath
            );
            
            $this->_storageClient->deleteBlob(
                $this->_container,
                $sourcePath
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on move: '.$e->getMessage(), $e->getCode(), $e);
        }
            
    }
    
    /**
     * Rename an item in the storage service to a given name.
     *
     *
     * @param  string $path
     * @param  string $name
     * @param  array $options
     * @return void
     */
    public function renameItem($path, $name, $options = null)
    {
        return $this->moveItem($path, $name, $options);
    }
    
    /**
     * List items in the given directory in the storage service
     * 
     * The $path must be a directory
     *
     *
     * @param  string $path Must be a directory
     * @param  array $options
     * @return array A list of item names
     */
    public function listItems($path, $options = null)
    {
        // Options
        $returnType = self::RETURN_NAMES; // 1: return list of paths, 2: return raw output from underlying provider
        
        // Parse options
        if (is_array($options)&& isset($options[self::RETURN_TYPE])) {
               $returnType = $options[self::RETURN_TYPE];
        }
        
        try {
            // Fetch list
            $blobList = $this->_storageClient->listBlobs(
                $this->_container,
                $path
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on list: '.$e->getMessage(), $e->getCode(), $e);
        }
        
        // Return
        if ($returnType == self::RETURN_LIST) {
            return $blobList;
        }
        
        $returnValue = array();
        foreach ($blobList as $blob) {
            $returnValue[] = $blob->Name;
        }
            
        return $returnValue;
    }

    /**
     * Get a key/value array of metadata for the given path.
     *
     * @param  string $path
     * @param  array  $options
     * @return array
     */
    public function fetchMetadata($path, $options = null)
    {
        try {
            return $this->_storageClient->getBlobMetaData(
                $this->_container,
                $path
            );
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "could not be accessed") !== false) {
                return false;
            }
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
    }
    
    /**
     * Store a key/value array of metadata at the given path.
     * WARNING: This operation overwrites any metadata that is located at 
     * $destinationPath.
     *
     * @param  string $destinationPath
     * @param  array $options
     * @return void
     */
    public function storeMetadata($destinationPath, $metadata, $options = null)
    {
        try    {
            $this->_storageClient->setBlobMetadata($this->_container, $destinationPath, $metadata);
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "could not be accessed") === false) {
                throw new Zend_Cloud_StorageService_Exception('Error on store metadata: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
    }
    
    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param  string $path
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path, $options = null)
    {
        try {
            $this->_storageClient->setBlobMetadata($this->_container, $destinationPath, array());
        } catch (Zend_Service_WindowsAzure_Exception $e) {
            if (strpos($e->getMessage(), "could not be accessed") === false) {
                throw new Zend_Cloud_StorageService_Exception('Error on delete metadata: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
    }
    
    /**
     * Delete container
     * 
     * @return void
     */
    public function deleteContainer()
    {
        try {
            $this->_storageClient->deleteContainer($this->_container);
        } catch (Zend_Service_WindowsAzure_Exception $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on delete: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get the concrete adapter.
     * @return Zend_Service_Azure_Storage_Blob
     */
    public function getClient()
    {
         return $this->_storageClient;       
    }
}
