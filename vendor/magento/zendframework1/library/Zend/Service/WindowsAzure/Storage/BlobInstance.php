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
 * @see Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Storage/StorageEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string  $Container       The name of the blob container in which the blob is stored.
 * @property string  $Name            The name of the blob.
 * @property string  $SnapshotId      The blob snapshot ID if it is a snapshot blob (= a backup copy of a blob).
 * @property string  $Etag            The entity tag, used for versioning and concurrency.
 * @property string  $LastModified    Timestamp when the blob was last modified.
 * @property string  $Url             The full URL where the blob can be downloaded.
 * @property int     $Size            The blob size in bytes.
 * @property string  $ContentType     The blob content type header.
 * @property string  $ContentEncoding The blob content encoding header.
 * @property string  $ContentLanguage The blob content language header.
 * @property string  $CacheControl    The blob cache control header.
 * @property string  $BlobType        The blob type (block blob / page blob).
 * @property string  $LeaseStatus     The blob lease status.
 * @property boolean $IsPrefix        Is it a blob or a directory prefix?
 * @property array   $Metadata        Key/value pairs of meta data
 */
class Zend_Service_WindowsAzure_Storage_BlobInstance
	extends Zend_Service_WindowsAzure_Storage_StorageEntityAbstract
{
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
}
