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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: BlobInstance.php 22773 2010-08-03 07:18:27Z maartenba $
 */

/**
 * @see Zend_Service_WindowsAzure_Exception
 */
#require_once 'Zend/Service/WindowsAzure/Exception.php';

/**
 * @see Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Storage/StorageEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * 
 * @property string  $Container       Container name
 * @property string  $Name            Name
 * @property string  $SnapshotId      Snapshot id
 * @property string  $Etag            Etag
 * @property string  $LastModified    Last modified date
 * @property string  $Url             Url
 * @property int     $Size            Size
 * @property string  $ContentType     Content Type
 * @property string  $ContentEncoding Content Encoding
 * @property string  $ContentLanguage Content Language
 * @property string  $CacheControl    Cache control
 * @property string  $BlobType        Blob type
 * @property string  $LeaseStatus     Lease status
 * @property boolean $IsPrefix        Is Prefix?
 * @property array   $Metadata        Key/value pairs of meta data
 */
class Zend_Service_WindowsAzure_Storage_BlobInstance
{
    /**
     * Data
     * 
     * @var array
     */
    protected $_data = null;
    
    /**
     * Constructor
     * 
     * @param string  $containerName   Container name
     * @param string  $name            Name
     * @param string  $snapshotId      Snapshot id
     * @param string  $etag            Etag
     * @param string  $lastModified    Last modified date
     * @param string  $url             Url
     * @param int     $size            Size
     * @param string  $contentType     Content Type
     * @param string  $contentEncoding Content Encoding
     * @param string  $contentLanguage Content Language
     * @param string  $cacheControl    Cache control
     * @param string  $blobType        Blob type
     * @param string  $leaseStatus     Lease status
     * @param boolean $isPrefix        Is Prefix?
     * @param array   $metadata        Key/value pairs of meta data
     */
    public function __construct($containerName, $name, $snapshotId, $etag, $lastModified, $url = '', $size = 0, $contentType = '', $contentEncoding = '', $contentLanguage = '', $cacheControl = '', $blobType = '', $leaseStatus = '', $isPrefix = false, $metadata = array()) 
    {	        
        $this->_data = array(
            'container'        => $containerName,
            'name'             => $name,
        	'snapshotid'	   => $snapshotId,
            'etag'             => $etag,
            'lastmodified'     => $lastModified,
            'url'              => $url,
            'size'             => $size,
            'contenttype'      => $contentType,
            'contentencoding'  => $contentEncoding,
            'contentlanguage'  => $contentLanguage,
            'cachecontrol'     => $cacheControl,
            'blobtype'         => $blobType,
            'leasestatus'      => $leaseStatus,
            'isprefix'         => $isPrefix,
            'metadata'         => $metadata
        );
    }
    
    /**
     * Magic overload for setting properties
     * 
     * @param string $name     Name of the property
     * @param string $value    Value to set
     */
    public function __set($name, $value) {
        if (array_key_exists(strtolower($name), $this->_data)) {
            $this->_data[strtolower($name)] = $value;
            return;
        }

        throw new Exception("Unknown property: " . $name);
    }

    /**
     * Magic overload for getting properties
     * 
     * @param string $name     Name of the property
     */
    public function __get($name) {
        if (array_key_exists(strtolower($name), $this->_data)) {
            return $this->_data[strtolower($name)];
        }

        throw new Exception("Unknown property: " . $name);
    }
}
