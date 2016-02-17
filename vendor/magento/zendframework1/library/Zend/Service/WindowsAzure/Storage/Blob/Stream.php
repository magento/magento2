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
 * @package    Zend_Service_WindowsAzure_Storage
 * @subpackage Blob
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://todo     name_todo
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_Storage
 * @subpackage Blob
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_Blob_Stream
{
    /**
     * Current file name
     * 
     * @var string
     */
    protected $_fileName = null;
    
    /**
     * Temporary file name
     * 
     * @var string
     */
    protected $_temporaryFileName = null;
    
    /**
     * Temporary file handle
     * 
     * @var resource
     */
    protected $_temporaryFileHandle = null;
    
    /**
     * Blob storage client
     * 
     * @var Zend_Service_WindowsAzure_Storage_Blob
     */
    protected $_storageClient = null;
    
    /**
     * Write mode?
     * 
     * @var boolean
     */
    protected $_writeMode = false;
    
    /**
     * List of blobs
     * 
     * @var array
     */
    protected $_blobs = null;
    
    /**
     * Retrieve storage client for this stream type
     * 
     * @param string $path
     * @return Zend_Service_WindowsAzure_Storage_Blob
     */
    protected function _getStorageClient($path = '')
    {
        if (is_null($this->_storageClient)) {
            $url = explode(':', $path);
            if (!$url) {
                throw new Zend_Service_WindowsAzure_Exception('Could not parse path "' . $path . '".');
            }

            $this->_storageClient = Zend_Service_WindowsAzure_Storage_Blob::getWrapperClient($url[0]);
            if (!$this->_storageClient) {
                throw new Zend_Service_WindowsAzure_Exception('No storage client registered for stream type "' . $url[0] . '://".');
            }
        }
        
        return $this->_storageClient;
    }
    
    /**
     * Extract container name
     *
     * @param string $path
     * @return string
     */
    protected function _getContainerName($path)
    {
        $url = parse_url($path);
        if ($url['host']) {
            return $url['host'];
        }

        return '';
    }
    
    /**
     * Extract file name
     *
     * @param string $path
     * @return string
     */
    protected function _getFileName($path)
    {
        $url = parse_url($path);
        if ($url['host']) {
            $fileName = isset($url['path']) ? $url['path'] : $url['host'];
    	    if (strpos($fileName, '/') === 0) {
    	        $fileName = substr($fileName, 1);
    	    }
            return $fileName;
        }

        return '';
    }
       
    /**
     * Open the stream
     *
     * @param  string  $path
     * @param  string  $mode
     * @param  integer $options
     * @param  string  $opened_path
     * @return boolean
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->_fileName = $path;
        $this->_temporaryFileName = tempnam(sys_get_temp_dir(), 'azure');
        
        // Check the file can be opened
        $fh = @fopen($this->_temporaryFileName, $mode);
        if ($fh === false) {
            return false;
        }
        fclose($fh);
        
        // Write mode?
        if (strpbrk($mode, 'wax+')) {
            $this->_writeMode = true;
    	} else {
            $this->_writeMode = false;
        }
        
        // If read/append, fetch the file
        if (!$this->_writeMode || strpbrk($mode, 'ra+')) {
            $this->_getStorageClient($this->_fileName)->getBlob(
                $this->_getContainerName($this->_fileName),
                $this->_getFileName($this->_fileName),
                $this->_temporaryFileName
            );
        }
        
        // Open temporary file handle
        $this->_temporaryFileHandle = fopen($this->_temporaryFileName, $mode);
        
        // Ok!
        return true;
    }

    /**
     * Close the stream
     *
     * @return void
     */
    public function stream_close()
    {
        @fclose($this->_temporaryFileHandle);
        
        // Upload the file?
        if ($this->_writeMode) {
            // Make sure the container exists
            $containerExists = $this->_getStorageClient($this->_fileName)->containerExists(
                $this->_getContainerName($this->_fileName)
            );
            if (!$containerExists) {
                $this->_getStorageClient($this->_fileName)->createContainer(
                    $this->_getContainerName($this->_fileName)
                );
            }
            
            // Upload the file
            try {
                $this->_getStorageClient($this->_fileName)->putBlob(
                    $this->_getContainerName($this->_fileName),
                    $this->_getFileName($this->_fileName),
                    $this->_temporaryFileName
                );
            } catch (Zend_Service_WindowsAzure_Exception $ex) {
                @unlink($this->_temporaryFileName);
                unset($this->_storageClient);
                
                throw $ex;
            }
        }
        
        @unlink($this->_temporaryFileName);
        unset($this->_storageClient);
    }

    /**
     * Read from the stream
     *
     * @param  integer $count
     * @return string
     */
    public function stream_read($count)
    {
        if (!$this->_temporaryFileHandle) {
            return false;
        }

        return fread($this->_temporaryFileHandle, $count);
    }

    /**
     * Write to the stream
     *
     * @param  string $data
     * @return integer
     */
    public function stream_write($data)
    {
        if (!$this->_temporaryFileHandle) {
            return 0;
        }
        
        $len = strlen($data);
        fwrite($this->_temporaryFileHandle, $data, $len);
        return $len;
    }

    /**
     * End of the stream?
     *
     * @return boolean
     */
    public function stream_eof()
    {
        if (!$this->_temporaryFileHandle) {
            return true;
        }

        return feof($this->_temporaryFileHandle);
    }

    /**
     * What is the current read/write position of the stream?
     *
     * @return integer
     */
    public function stream_tell()
    {
        return ftell($this->_temporaryFileHandle);
    }

    /**
     * Update the read/write position of the stream
     *
     * @param  integer $offset
     * @param  integer $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        if (!$this->_temporaryFileHandle) {
            return false;
        }
        
        return (fseek($this->_temporaryFileHandle, $offset, $whence) === 0);
    }

    /**
     * Flush current cached stream data to storage
     *
     * @return boolean
     */
    public function stream_flush()
    {
        $result = fflush($this->_temporaryFileHandle);
        
         // Upload the file?
        if ($this->_writeMode) {
            // Make sure the container exists
            $containerExists = $this->_getStorageClient($this->_fileName)->containerExists(
                $this->_getContainerName($this->_fileName)
            );
            if (!$containerExists) {
                $this->_getStorageClient($this->_fileName)->createContainer(
                    $this->_getContainerName($this->_fileName)
                );
            }
            
            // Upload the file
            try {
                $this->_getStorageClient($this->_fileName)->putBlob(
                    $this->_getContainerName($this->_fileName),
                    $this->_getFileName($this->_fileName),
                    $this->_temporaryFileName
                );
            } catch (Zend_Service_WindowsAzure_Exception $ex) {
                @unlink($this->_temporaryFileName);
                unset($this->_storageClient);
                
                throw $ex;
            }
        }
        
        return $result;
    }

    /**
     * Returns data array of stream variables
     *
     * @return array
     */
    public function stream_stat()
    {
        if (!$this->_temporaryFileHandle) {
            return false;
        }

        return $this->url_stat($this->_fileName, 0);
    }

    /**
     * Attempt to delete the item
     *
     * @param  string $path
     * @return boolean
     */
    public function unlink($path)
    {
        $this->_getStorageClient($path)->deleteBlob(
            $this->_getContainerName($path),
            $this->_getFileName($path)
        );

        // Clear the stat cache for this path.
        clearstatcache(true, $path);
        return true;
    }

    /**
     * Attempt to rename the item
     *
     * @param  string  $path_from
     * @param  string  $path_to
     * @return boolean False
     */
    public function rename($path_from, $path_to)
    {
        if ($this->_getContainerName($path_from) != $this->_getContainerName($path_to)) {
            throw new Zend_Service_WindowsAzure_Exception('Container name can not be changed.');
        }
        
        if ($this->_getFileName($path_from) == $this->_getContainerName($path_to)) {
            return true;
        }
            
        $this->_getStorageClient($path_from)->copyBlob(
            $this->_getContainerName($path_from),
            $this->_getFileName($path_from),
            $this->_getContainerName($path_to),
            $this->_getFileName($path_to)
        );
        $this->_getStorageClient($path_from)->deleteBlob(
            $this->_getContainerName($path_from),
            $this->_getFileName($path_from)
        );

        // Clear the stat cache for the affected paths.
        clearstatcache(true, $path_from);
        clearstatcache(true, $path_to);
        return true;
    }
    
    /**
     * Return array of URL variables
     *
     * @param  string $path
     * @param  integer $flags
     * @return array
     */
    public function url_stat($path, $flags)
    {
        $stat = array();
        $stat['dev'] = 0;
        $stat['ino'] = 0;
        $stat['mode'] = 0;
        $stat['nlink'] = 0;
        $stat['uid'] = 0;
        $stat['gid'] = 0;
        $stat['rdev'] = 0;
        $stat['size'] = 0;
        $stat['atime'] = 0;
        $stat['mtime'] = 0;
        $stat['ctime'] = 0;
        $stat['blksize'] = 0;
        $stat['blocks'] = 0;

        $info = null;
        try {
            $info = $this->_getStorageClient($path)->getBlobInstance(
                        $this->_getContainerName($path),
                        $this->_getFileName($path)
                    );
            $stat['size']  = $info->Size;

            // Set the modification time and last modified to the Last-Modified header.
            $lastmodified = strtotime($info->LastModified);
            $stat['mtime'] = $lastmodified;
            $stat['ctime'] = $lastmodified;

            // Entry is a regular file.
            $stat['mode'] = 0100000;

            return array_values($stat) + $stat;
        } catch (Zend_Service_WindowsAzure_Exception $ex) {
            // Unexisting file...
            return false;
        }
    }

    /**
     * Create a new directory
     *
     * @param  string  $path
     * @param  integer $mode
     * @param  integer $options
     * @return boolean
     */
    public function mkdir($path, $mode, $options)
    {
        if ($this->_getContainerName($path) == $this->_getFileName($path)) {
            // Create container
            try {
                $this->_getStorageClient($path)->createContainer(
                    $this->_getContainerName($path)
                );
                return true;
            } catch (Zend_Service_WindowsAzure_Exception $ex) {
                return false;
            }
        } else {
            throw new Zend_Service_WindowsAzure_Exception('mkdir() with multiple levels is not supported on Windows Azure Blob Storage.');
        }
    }

    /**
     * Remove a directory
     *
     * @param  string  $path
     * @param  integer $options
     * @return boolean
     */
    public function rmdir($path, $options)
    {
        if ($this->_getContainerName($path) == $this->_getFileName($path)) {
            // Clear the stat cache so that affected paths are refreshed.
            clearstatcache();

            // Delete container
            try {
                $this->_getStorageClient($path)->deleteContainer(
                    $this->_getContainerName($path)
                );
                return true;
            } catch (Zend_Service_WindowsAzure_Exception $ex) {
                return false;
            }
        } else {
            throw new Zend_Service_WindowsAzure_Exception('rmdir() with multiple levels is not supported on Windows Azure Blob Storage.');
        }
    }

    /**
     * Attempt to open a directory
     *
     * @param  string $path
     * @param  integer $options
     * @return boolean
     */
    public function dir_opendir($path, $options)
    {
        $this->_blobs = $this->_getStorageClient($path)->listBlobs(
            $this->_getContainerName($path)
        );
        return is_array($this->_blobs);
    }

    /**
     * Return the next filename in the directory
     *
     * @return string
     */
    public function dir_readdir()
    {
        $object = current($this->_blobs);
        if ($object !== false) {
            next($this->_blobs);
            return $object->Name;
        }
        return false;
    }

    /**
     * Reset the directory pointer
     *
     * @return boolean True
     */
    public function dir_rewinddir()
    {
        reset($this->_blobs);
        return true;
    }

    /**
     * Close a directory
     *
     * @return boolean True
     */
    public function dir_closedir()
    {
        $this->_blobs = null;
        return true;
    }
}
