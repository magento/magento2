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
 * @package    Zend_Service
 * @subpackage Rackspace
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Abstract.php';
#require_once 'Zend/Service/Rackspace/Files/ContainerList.php';
#require_once 'Zend/Service/Rackspace/Files/ObjectList.php';
#require_once 'Zend/Service/Rackspace/Files/Container.php';
#require_once 'Zend/Service/Rackspace/Files/Object.php';

/**
 * Zend_Service_Rackspace_Files
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Rackspace
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Rackspace_Files extends Zend_Service_Rackspace_Abstract
{
    const ERROR_CONTAINER_NOT_EMPTY            = 'The container is not empty, I cannot delete it.';
    const ERROR_CONTAINER_NOT_FOUND            = 'The container was not found.';
    const ERROR_OBJECT_NOT_FOUND               = 'The object was not found.';
    const ERROR_OBJECT_MISSING_PARAM           = 'Missing Content-Length or Content-Type header in the request';
    const ERROR_OBJECT_CHECKSUM                = 'Checksum of the file content failed';
    const ERROR_CONTAINER_EXIST                = 'The container already exists';
    const ERROR_PARAM_NO_NAME_CONTAINER        = 'You must specify the container name';
    const ERROR_PARAM_NO_NAME_OBJECT           = 'You must specify the object name';
    const ERROR_PARAM_NO_CONTENT               = 'You must specify the content of the object';
    const ERROR_PARAM_NO_NAME_SOURCE_CONTAINER = 'You must specify the source container name';
    const ERROR_PARAM_NO_NAME_SOURCE_OBJECT    = 'You must specify the source object name';
    const ERROR_PARAM_NO_NAME_DEST_CONTAINER   = 'You must specify the destination container name';
    const ERROR_PARAM_NO_NAME_DEST_OBJECT      = 'You must specify the destination object name';
    const ERROR_PARAM_NO_METADATA              = 'You must specify the metadata array';
    const ERROR_CDN_TTL_OUT_OF_RANGE           = 'TTL must be a number in seconds, min is 900 sec and maximum is 1577836800 (50 years)';
    const ERROR_PARAM_UPDATE_CDN               = 'You must specify at least one the parameters: ttl, cdn_enabled or log_retention';
    const HEADER_CONTENT_TYPE                  = 'Content-Type';
    const HEADER_HASH                          = 'Etag';
    const HEADER_LAST_MODIFIED                 = 'Last-Modified';
    const HEADER_CONTENT_LENGTH                = 'Content-Length';
    const HEADER_COPY_FROM                     = 'X-Copy-From';
    const METADATA_OBJECT_HEADER               = "X-Object-Meta-";
    const METADATA_CONTAINER_HEADER            = "X-Container-Meta-";
    const CDN_URI                              = "X-CDN-URI";
    const CDN_SSL_URI                          = "X-CDN-SSL-URI";
    const CDN_ENABLED                          = "X-CDN-Enabled";
    const CDN_LOG_RETENTION                    = "X-Log-Retention";
    const CDN_ACL_USER_AGENT                   = "X-User-Agent-ACL";
    const CDN_ACL_REFERRER                     = "X-Referrer-ACL";
    const CDN_TTL                              = "X-TTL";
    const CDN_TTL_MIN                          = 900;
    const CDN_TTL_MAX                          = 1577836800;
    const CDN_EMAIL                            = "X-Purge-Email";
    const ACCOUNT_CONTAINER_COUNT              = "X-Account-Container-Count";
    const ACCOUNT_BYTES_USED                   = "X-Account-Bytes-Used";
    const ACCOUNT_OBJ_COUNT                    = "X-Account-Object-Count";
    const CONTAINER_OBJ_COUNT                  = "X-Container-Object-Count";
    const CONTAINER_BYTES_USE                  = "X-Container-Bytes-Used";
    const MANIFEST_OBJECT_HEADER               = "X-Object-Manifest";

    /**
     * Return the total count of containers
     *
     * @return int
     */
    public function getCountContainers()
    {
        $data= $this->getInfoAccount();
        return $data['tot_containers'];
    }
    /**
     * Return the size in bytes of all the containers
     *
     * @return int
     */
    public function getSizeContainers()
    {
        $data= $this->getInfoAccount();
        return $data['size_containers'];
    }
    /**
     * Return the count of objects contained in all the containers
     *
     * @return int
     */
    public function getCountObjects()
    {
        $data= $this->getInfoAccount();
        return $data['tot_objects'];
    }
    /**
     * Get all the containers
     *
     * @param  array $options
     * @return Zend_Service_Rackspace_Files_ContainerList|bool
     */
    public function getContainers($options=array())
    {
        $result= $this->httpCall($this->getStorageUrl(),'GET',null,$options);
        if ($result->isSuccessful()) {
            return new Zend_Service_Rackspace_Files_ContainerList($this,json_decode($result->getBody(),true));
        }
        return false;
    }
    /**
     * Get all the CDN containers
     *
     * @param array $options
     * @return array|bool
     */
    public function getCdnContainers($options=array())
    {
        $options['enabled_only']= true;
        $result= $this->httpCall($this->getCdnUrl(),'GET',null,$options);
        if ($result->isSuccessful()) {
            return new Zend_Service_Rackspace_Files_ContainerList($this,json_decode($result->getBody(),true));
        }
        return false;
    }
    /**
     * Get the metadata information of the accounts:
     * - total count containers
     * - size in bytes of all the containers
     * - total objects in all the containers
     * 
     * @return array|bool
     */
    public function getInfoAccount()
    {
        $result= $this->httpCall($this->getStorageUrl(),'HEAD');
        if ($result->isSuccessful()) {
            $output= array(
                'tot_containers'  => $result->getHeader(self::ACCOUNT_CONTAINER_COUNT),
                'size_containers' => $result->getHeader(self::ACCOUNT_BYTES_USED),
                'tot_objects'     => $result->getHeader(self::ACCOUNT_OBJ_COUNT)
            );
            return $output;
        }
        return false;
    }

    /**
     * Get all the objects of a container
     *
     * Returns a maximum of 10,000 object names.
     *
     * @param  string $container
     * @param  array  $options
     * @return bool|Zend_Service_Rackspace_Files_ObjectList
     * @throws Zend_Service_Rackspace_Exception
     */
    public function getObjects($container,$options=array())
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container),'GET',null,$options);
        if ($result->isSuccessful()) {
            return new Zend_Service_Rackspace_Files_ObjectList($this,json_decode($result->getBody(),true),$container);
        }
        return false;
    }

    /**
     * Create a container
     *
     * @param  string $container
     * @param  array  $metadata
     * @return bool|Zend_Service_Rackspace_Files_Container
     * @throws Zend_Service_Rackspace_Exception
     */
    public function createContainer($container,$metadata=array())
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $headers=array();
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $headers[self::METADATA_CONTAINER_HEADER.rawurlencode(strtolower($key))]= rawurlencode($value);
            }
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container),'PUT',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '201': // break intentionally omitted
                $data= array(
                    'name' => $container
                );
                return new Zend_Service_Rackspace_Files_Container($this,$data);
            case '202':
                $this->errorMsg= self::ERROR_CONTAINER_EXIST;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Delete a container (only if it's empty)
     *
     * @param  string $container
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function deleteContainer($container)
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container),'DELETE');
        $status= $result->getStatus();
        switch ($status) {
            case '204': // break intentionally omitted
                return true;
            case '409':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_EMPTY;
                break;
            case '404':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Get the metadata of a container
     *
     * @param  string $container
     * @return array|bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function getMetadataContainer($container)
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container),'HEAD');
        $status= $result->getStatus();
        switch ($status) {
            case '204': // break intentionally omitted
                $headers= $result->getHeaders();
                $count= strlen(self::METADATA_CONTAINER_HEADER);
                // Zend_Http_Response alters header name in array key, so match our header to what will be in the headers array
                $headerName = ucwords(strtolower(self::METADATA_CONTAINER_HEADER));
                $metadata= array();
                foreach ($headers as $type => $value) {
                    if (strpos($type,$headerName)!==false) {
                        $metadata[strtolower(substr($type, $count))]= $value;
                    }
                }
                $data= array (
                    'name'     => $container,
                    'count'    => $result->getHeader(self::CONTAINER_OBJ_COUNT),
                    'bytes'    => $result->getHeader(self::CONTAINER_BYTES_USE),
                    'metadata' => $metadata
                );
                return $data;
            case '404':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
    /**
     * Get a container
     * 
     * @param  string $container
     * @return Zend_Service_Rackspace_Files_Container|bool
     */
    public function getContainer($container) {
        $result= $this->getMetadataContainer($container);
        if (!empty($result)) {
            return new Zend_Service_Rackspace_Files_Container($this,$result);
        }
        return false;
    }

    /**
     * Get an object in a container
     *
     * @param  string $container
     * @param  string $object
     * @param  array  $headers
     * @return bool|Zend_Service_Rackspace_Files_Object
     * @throws Zend_Service_Rackspace_Exception
     */
    public function getObject($container,$object,$headers=array())
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($object)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container).'/'.rawurlencode($object),'GET',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '200': // break intentionally omitted
                $data= array(
                    'name'          => $object,
                    'container'     => $container,
                    'hash'          => $result->getHeader(self::HEADER_HASH),
                    'bytes'         => $result->getHeader(self::HEADER_CONTENT_LENGTH),
                    'last_modified' => $result->getHeader(self::HEADER_LAST_MODIFIED),
                    'content_type'  => $result->getHeader(self::HEADER_CONTENT_TYPE),
                    'content'       => $result->getBody()
                );
                return new Zend_Service_Rackspace_Files_Object($this,$data);
            case '404':
                $this->errorMsg= self::ERROR_OBJECT_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Store a file in a container
     *
     * @param  string $container
     * @param  string $object
     * @param  string $content
     * @param  array  $metadata
     * @param  string $content_type
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function storeObject($container,$object,$content,$metadata=array(),$content_type=null) {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($object)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        if (empty($content)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_CONTENT);
        }
        if (!empty($content_type)) {
           $headers[self::HEADER_CONTENT_TYPE]= $content_type;
        }
        if (!empty($metadata) && is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $headers[self::METADATA_OBJECT_HEADER.$key]= $value;
            }
        }
        $headers[self::HEADER_HASH]= md5($content);
        $headers[self::HEADER_CONTENT_LENGTH]= strlen($content);
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container).'/'.rawurlencode($object),'PUT',$headers,null,$content);
        $status= $result->getStatus();
        switch ($status) {
            case '201': // break intentionally omitted
                return true;
            case '412':
                $this->errorMsg= self::ERROR_OBJECT_MISSING_PARAM;
                break;
            case '422':
                $this->errorMsg= self::ERROR_OBJECT_CHECKSUM;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Delete an object in a container
     *
     * @param  string $container
     * @param  string $object
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function deleteObject($container,$object) {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($object)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container).'/'.rawurlencode($object),'DELETE');
        $status= $result->getStatus();
        switch ($status) {
            case '204': // break intentionally omitted
                return true;
            case '404':
                $this->errorMsg= self::ERROR_OBJECT_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Copy an object from a container to another
     *
     * @param  string $container_source
     * @param  string $obj_source
     * @param  string $container_dest
     * @param  string $obj_dest
     * @param  array  $metadata
     * @param  string $content_type
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function copyObject($container_source,$obj_source,$container_dest,$obj_dest,$metadata=array(),$content_type=null) {
        if (empty($container_source)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_SOURCE_CONTAINER);
        }
        if (empty($obj_source)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_SOURCE_OBJECT);
        }
        if (empty($container_dest)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_DEST_CONTAINER);
        }
        if (empty($obj_dest)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_DEST_OBJECT);
        }
        $headers= array(
            self::HEADER_COPY_FROM => '/'.rawurlencode($container_source).'/'.rawurlencode($obj_source),
            self::HEADER_CONTENT_LENGTH => 0
        );
        if (!empty($content_type)) {
            $headers[self::HEADER_CONTENT_TYPE]= $content_type;
        }
        if (!empty($metadata) && is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $headers[self::METADATA_OBJECT_HEADER.$key]= $value;
            }
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container_dest).'/'.rawurlencode($obj_dest),'PUT',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '201': // break intentionally omitted
                return true;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Get the metadata of an object
     *
     * @param  string $container
     * @param  string $object
     * @return array|bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function getMetadataObject($container,$object) {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($object)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container).'/'.rawurlencode($object),'HEAD');
        $status= $result->getStatus();
        switch ($status) {
            case '200': // break intentionally omitted
                $headers= $result->getHeaders();
                $count= strlen(self::METADATA_OBJECT_HEADER);
                // Zend_Http_Response alters header name in array key, so match our header to what will be in the headers array
                $headerName = ucwords(strtolower(self::METADATA_OBJECT_HEADER));
                $metadata= array();
                foreach ($headers as $type => $value) {
                    if (strpos($type,$headerName)!==false) {
                        $metadata[strtolower(substr($type, $count))]= $value;
                    }
                }
                $data= array (
                    'name'          => $object,
                    'container'     => $container,
                    'hash'          => $result->getHeader(self::HEADER_HASH),
                    'bytes'         => $result->getHeader(self::HEADER_CONTENT_LENGTH),
                    'content_type'  => $result->getHeader(self::HEADER_CONTENT_TYPE),
                    'last_modified' => $result->getHeader(self::HEADER_LAST_MODIFIED),
                    'metadata'      => $metadata
                );
                return $data;
            case '404':
                $this->errorMsg= self::ERROR_OBJECT_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Set the metadata of a object in a container
     * The old metadata values are replaced with the new one
     *
     * @param  string $container
     * @param  string $object
     * @param  array  $metadata
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function setMetadataObject($container,$object,$metadata)
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($object)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        if (empty($metadata) || !is_array($metadata)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_OBJECT);
        }
        $headers=array();
        foreach ($metadata as $key => $value) {
            $headers[self::METADATA_OBJECT_HEADER.$key]= $value;
        }
        $result= $this->httpCall($this->getStorageUrl().'/'.rawurlencode($container).'/'.rawurlencode($object),'POST',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '202': // break intentionally omitted
                return true;
            case '404':
                $this->errorMsg= self::ERROR_OBJECT_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Enable the CDN for a container
     *
     * @param  string $container
     * @param  int    $ttl
     * @return array|bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function enableCdnContainer ($container,$ttl=self::CDN_TTL_MIN) {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $headers=array();
        if (is_numeric($ttl) && ($ttl>=self::CDN_TTL_MIN) && ($ttl<=self::CDN_TTL_MAX)) {
            $headers[self::CDN_TTL]= $ttl;
        } else {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_CDN_TTL_OUT_OF_RANGE);
        }
        $result= $this->httpCall($this->getCdnUrl().'/'.rawurlencode($container),'PUT',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '201':
            case '202': // break intentionally omitted
                $data= array (
                    'cdn_uri'     => $result->getHeader(self::CDN_URI),
                    'cdn_uri_ssl' => $result->getHeader(self::CDN_SSL_URI)
                );
                return $data;
            case '404':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Update the attribute of a CDN container
     *
     * @param  string $container
     * @param  int    $ttl
     * @param  bool   $cdn_enabled
     * @param  bool   $log
     * @return bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function updateCdnContainer($container,$ttl=null,$cdn_enabled=null,$log=null)
    {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        if (empty($ttl) && (!isset($cdn_enabled)) && (!isset($log))) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_UPDATE_CDN);
        }
        $headers=array();
        if (isset($ttl)) {
            if (is_numeric($ttl) && ($ttl>=self::CDN_TTL_MIN) && ($ttl<=self::CDN_TTL_MAX)) {
                $headers[self::CDN_TTL]= $ttl;
            } else {
                #require_once 'Zend/Service/Rackspace/Exception.php';
                throw new Zend_Service_Rackspace_Exception(self::ERROR_CDN_TTL_OUT_OF_RANGE);
            }
        }
        if (isset($cdn_enabled)) {
            if ($cdn_enabled===true) {
                $headers[self::CDN_ENABLED]= 'true';
            } else {
                $headers[self::CDN_ENABLED]= 'false';
            }
        }
        if (isset($log)) {
            if ($log===true) {
                $headers[self::CDN_LOG_RETENTION]= 'true';
            } else  {
                $headers[self::CDN_LOG_RETENTION]= 'false';
            }
        }
        $result= $this->httpCall($this->getCdnUrl().'/'.rawurlencode($container),'POST',$headers);
        $status= $result->getStatus();
        switch ($status) {
            case '200':
            case '202': // break intentionally omitted
                return true;
            case '404':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }

    /**
     * Get the information of a Cdn container
     *
     * @param  string $container
     * @return array|bool
     * @throws Zend_Service_Rackspace_Exception
     */
    public function getInfoCdnContainer($container) {
        if (empty($container)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception(self::ERROR_PARAM_NO_NAME_CONTAINER);
        }
        $result= $this->httpCall($this->getCdnUrl().'/'.rawurlencode($container),'HEAD');
        $status= $result->getStatus();
        switch ($status) {
            case '204': // break intentionally omitted
                $data= array (
                    'ttl'         => $result->getHeader(self::CDN_TTL),
                    'cdn_uri'     => $result->getHeader(self::CDN_URI),
                    'cdn_uri_ssl' => $result->getHeader(self::CDN_SSL_URI)
                );
                $data['cdn_enabled']= (strtolower($result->getHeader(self::CDN_ENABLED))!=='false');
                $data['log_retention']= (strtolower($result->getHeader(self::CDN_LOG_RETENTION))!=='false');
                return $data;
            case '404':
                $this->errorMsg= self::ERROR_CONTAINER_NOT_FOUND;
                break;
            default:
                $this->errorMsg= $result->getBody();
                break;
        }
        $this->errorCode= $status;
        return false;
    }
}
