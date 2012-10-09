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
#require_once 'Zend/Cloud/StorageService/Exception.php';
#require_once 'Zend/Service/Nirvanix.php';

/**
 * Adapter for Nirvanix cloud storage
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage StorageService
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_StorageService_Adapter_Nirvanix 
    implements Zend_Cloud_StorageService_Adapter
{
    const USERNAME         = 'auth_username';
    const PASSWORD         = 'auth_password';
    const APP_KEY          = 'auth_accesskey';
    const REMOTE_DIRECTORY = 'remote_directory';

    /**
     * The Nirvanix adapter
     * @var Zend_Service_Nirvanix
     */
    protected $_nirvanix;
    protected $_imfNs;
    protected $_metadataNs;
    protected $_remoteDirectory;
    private $maxPageSize = 500;

    /**
     * Constructor
     * 
     * @param  array|Zend_Config $options 
     * @return void
     */
    function __construct($options = array()) 
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (!is_array($options)) {
            throw new Zend_Cloud_StorageService_Exception('Invalid options provided');
        }

        $auth = array(
            'username' => $options[self::USERNAME],
            'password' => $options[self::PASSWORD],
            'appKey'   => $options[self::APP_KEY],
        );
        $nirvanix_options = array();
        if (isset($options[self::HTTP_ADAPTER])) {
            $httpc = new Zend_Http_Client();
            $httpc->setAdapter($options[self::HTTP_ADAPTER]);
            $nirvanix_options['httpClient'] = $httpc;
        } 
        try {
            $this->_nirvanix = new Zend_Service_Nirvanix($auth, $nirvanix_options);
            $this->_remoteDirectory = $options[self::REMOTE_DIRECTORY];
            $this->_imfNs = $this->_nirvanix->getService('IMFS');
            $this->_metadataNs = $this->_nirvanix->getService('Metadata');
        } catch (Zend_Service_Nirvanix_Exception  $e) { 
            throw new Zend_Cloud_StorageService_Exception('Error on create: '.$e->getMessage(), $e->getCode(), $e);
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
        $path = $this->_getFullPath($path);
        try {
            $item = $this->_imfNs->getContents($path);
        } catch (Zend_Service_Nirvanix_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetch: '.$e->getMessage(), $e->getCode(), $e);
        }
        return $item;
    }

    /**
     * Store an item in the storage service.
     * WARNING: This operation overwrites any item that is located at
     * $destinationPath.
     * @param string $destinationPath
     * @param mixed $data
     * @param  array $options
     * @return void
     */
    public function storeItem($destinationPath, $data, $options = null)
    {
        try {
            $path = $this->_getFullPath($destinationPath);
            $this->_imfNs->putContents($path, $data);
        } catch (Zend_Service_Nirvanix_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on store: '.$e->getMessage(), $e->getCode(), $e);
        }
        return true;
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
        try {
            $path = $this->_getFullPath($path);
            $this->_imfNs->unlink($path);
        } catch(Zend_Service_Nirvanix_Exception $e) {
//            if (trim(strtoupper($e->getMessage())) != 'INVALID PATH') {
//                // TODO Differentiate among errors in the Nirvanix adapter
            throw new Zend_Cloud_StorageService_Exception('Error on delete: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Copy an item in the storage service to a given path.
     * WARNING: This operation is *very* expensive for services that do not
     * support copying an item natively.
     *
     * @param  string $sourcePath
     * @param  string $destination path
     * @param  array $options
     * @return void
     */
    public function copyItem($sourcePath, $destinationPath, $options = null)
    {
        try {
            $sourcePath = $this->_getFullPath($sourcePath);
            $destinationPath = $this->_getFullPath($destinationPath);
            $this->_imfNs->CopyFiles(array('srcFilePath' => $sourcePath,
                                            'destFolderPath' => $destinationPath));
        } catch (Zend_Service_Nirvanix_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on copy: '.$e->getMessage(), $e->getCode(), $e);
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
            $sourcePath = $this->_getFullPath($sourcePath);
            $destinationPath = $this->_getFullPath($destinationPath);
            $this->_imfNs->RenameFile(array('filePath' => $sourcePath,
                                             'newFileName' => $destinationPath));
    //        $this->_imfNs->MoveFiles(array('srcFilePath' => $sourcePath,
    //                                         'destFolderPath' => $destinationPath));
        } catch (Zend_Service_Nirvanix_Exception $e) {
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
        $path = $this->_getFullPath($path);
        try {
            $metadataNode = $this->_metadataNs->getMetadata(array('path' => $path));
        } catch (Zend_Service_Nirvanix_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on fetching metadata: '.$e->getMessage(), $e->getCode(), $e);
        }
            
        $metadata = array();
        $length = count($metadataNode->Metadata);

        // Need to special case this as Nirvanix returns an array if there is
        // more than one, but doesn't return an array if there is only one.
        if ($length == 1)
        {
            $metadata[(string)$metadataNode->Metadata->Type->value] = (string)$metadataNode->Metadata->Value;
        }
        else if ($length > 1)
        {
            for ($i=0; $i<$length; $i++)
            {
                $metadata[(string)$metadataNode->Metadata[$i]->Type] = (string)$metadataNode->Metadata[$i]->Value;
            }
        }
        return $metadata;
    }

    /**
     * Store a key/value array of metadata at the given path.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath.
     *
     * @param array $metadata - An associative array specifying the key/value pairs for the metadata.
     * @param $destinationPath
     * @param  array $options
     * @return void
     */
    public function storeMetadata($destinationPath, $metadata, $options = null)
    {
        $destinationPath = $this->_getFullPath($destinationPath);
        if ($metadata != null) {
            try {
                foreach ($metadata AS $key=>$value) {
                    $metadataString = $key . ":" . $value;
                    $this->_metadataNs->SetMetadata(array(
                        'path'     => $destinationPath,
                        'metadata' => $metadataString,
                    ));
                }
            } catch (Zend_Service_Nirvanix_Exception $e) {
                throw new Zend_Cloud_StorageService_Exception('Error on storing metadata: '.$e->getMessage(), $e->getCode(), $e);
            }
        }
     }

    /**
     * Delete a key/value array of metadata at the given path.
     *
     * @param string $path
     * @param array $metadata - An associative array specifying the key/value pairs for the metadata
     *                          to be deleted.  If null, all metadata associated with the object will
     *                          be deleted.
     * @param  array $options
     * @return void
     */
    public function deleteMetadata($path, $metadata = null, $options = null)
    {
        $path = $this->_getFullPath($path);
        try {
            if ($metadata == null) {
                $this->_metadataNs->DeleteAllMetadata(array('path' => $path));
            } else {
                foreach ($metadata AS $key=>$value) {
                    $this->_metadataNs->DeleteMetadata(array(
                        'path'     => $path,
                        'metadata' => $key,
                    ));
                    }
            }
        } catch (Zend_Service_Nirvanix_Exception $e) {
            throw new Zend_Cloud_StorageService_Exception('Error on deleting metadata: '.$e->getMessage(), $e->getCode(), $e);
        }            
    }

    /*
     * Recursively traverse all the folders and build an array that contains
     * the path names for each folder.
     *
     * @param $path - The folder path to get the list of folders from.
     * @param &$resultArray - reference to the array that contains the path names
     *                           for each folder.
     */
    private function getAllFolders($path, &$resultArray)
    {
        $response = $this->_imfNs->ListFolder(array(
            'folderPath' => $path,
               'pageNumber' => 1,
            'pageSize'   => $this->maxPageSize,
        ));
           $numFolders = $response->ListFolder->TotalFolderCount;
           if ($numFolders == 0) {
               return;
           } else {
               //Need to special case this as Nirvanix returns an array if there is
               //more than one, but doesn't return an array if there is only one.
            if ($numFolders == 1) {
                $folderPath = $response->ListFolder->Folder->Path;
                array_push($resultArray, $folderPath);
                $this->getAllFolders('/' . $folderPath, $resultArray);
            } else {
                foreach ($response->ListFolder->Folder as $arrayElem) {
                    $folderPath = $arrayElem->Path;
                    array_push($resultArray, $folderPath);
                    $this->getAllFolders('/' . $folderPath, $resultArray);
                }
            }
           }
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
        $path = $this->_getFullPath($path);
        $resultArray = array();

        if (!isset($path)) {
            return false;
        } else {
            try {
                $response = $this->_imfNs->ListFolder(array(
                    'folderPath' => $path,
                    'pageNumber' => 1,
                    'pageSize'   => $this->maxPageSize,
                ));
            } catch (Zend_Service_Nirvanix_Exception $e) {
                throw new Zend_Cloud_StorageService_Exception('Error on list: '.$e->getMessage(), $e->getCode(), $e);
            }            
                
            $numFiles = $response->ListFolder->TotalFileCount;

            //Add the file names to the array
            if ($numFiles != 0) {
                //Need to special case this as Nirvanix returns an array if there is
                //more than one, but doesn't return an array if there is only one.
                if ($numFiles == 1) {
                    $resultArray[] = (string)$response->ListFolder->File->Name;
                }
                else {
                    foreach ($response->ListFolder->File as $arrayElem) {
                        $resultArray[] = (string) $arrayElem->Name;
                    }
                }
            }
        }

        return $resultArray;
    }

    /**
     * Get full path to an object
     * 
     * @param  string $path 
     * @return string
     */
    private function _getFullPath($path) 
    {
        return $this->_remoteDirectory . $path;
    }

    /**
     * Get the concrete client.
     * @return Zend_Service_Nirvanix
     */
    public function getClient()
    {
         return $this->_nirvanix;       
    }
}
