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
 * @package    Zend_Service_Rackspace
 * @subpackage Files
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Files.php';

class Zend_Service_Rackspace_Files_Container
{
    const ERROR_PARAM_FILE_CONSTRUCT = 'The Zend_Service_Rackspace_Files passed in construction is not valid';

    const ERROR_PARAM_ARRAY_CONSTRUCT = 'The array passed in construction is not valid';

    const ERROR_PARAM_NO_NAME = 'The container name is empty';

    /**
     * @var string
     */
    protected $name;

    /**
     * Construct
     *
     * @param Zend_Service_Rackspace_Files $service
     * @param                              $data
     *
     * @throws Zend_Service_Rackspace_Files_Exception
     */
    public function __construct($service, $data)
    {
        if (!($service instanceof Zend_Service_Rackspace_Files)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception(
                self::ERROR_PARAM_FILE_CONSTRUCT
            );
        }
        if (!is_array($data)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception(
                self::ERROR_PARAM_ARRAY_CONSTRUCT
            );
        }
        if (!array_key_exists('name', $data)) {
            #require_once 'Zend/Service/Rackspace/Files/Exception.php';
            throw new Zend_Service_Rackspace_Files_Exception(
                self::ERROR_PARAM_NO_NAME
            );
        }
        $this->service = $service;
        $this->name    = $data['name'];
    }

    /**
     * Get the name of the container
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the size in bytes of the container
     *
     * @return integer|bool
     */
    public function getSize()
    {
        $data = $this->getInfo();
        if (isset($data['bytes'])) {
            return $data['bytes'];
        }

        return false;
    }

    /**
     * Get the total count of objects in the container
     *
     * @return integer|bool
     */
    public function getObjectCount()
    {
        $data = $this->getInfo();
        if (isset($data['count'])) {
            return $data['count'];
        }

        return false;
    }

    /**
     * Return true if the container is CDN enabled
     *
     * @return bool
     */
    public function isCdnEnabled()
    {
        $data = $this->getCdnInfo();
        if (isset($data['cdn_enabled'])) {
            return $data['cdn_enabled'];
        }

        return false;
    }

    /**
     * Get the TTL of the CDN
     *
     * @return integer|bool
     */
    public function getCdnTtl()
    {
        $data = $this->getCdnInfo();
        if (isset($data['ttl'])) {
            return $data['ttl'];
        }

        return false;
    }

    /**
     * Return true if the log retention is enabled for the CDN
     *
     * @return bool
     */
    public function isCdnLogEnabled()
    {
        $data = $this->getCdnInfo();
        if (isset($data['log_retention'])) {
            return $data['log_retention'];
        }

        return false;
    }

    /**
     * Get the CDN URI
     *
     * @return string|bool
     */
    public function getCdnUri()
    {
        $data = $this->getCdnInfo();
        if (isset($data['cdn_uri'])) {
            return $data['cdn_uri'];
        }

        return false;
    }

    /**
     * Get the CDN URI SSL
     *
     * @return string|bool
     */
    public function getCdnUriSsl()
    {
        $data = $this->getCdnInfo();
        if (isset($data['cdn_uri_ssl'])) {
            return $data['cdn_uri_ssl'];
        }

        return false;
    }

    /**
     * Get the metadata of the container
     *
     * If $key is empty return the array of metadata
     *
     * @param string $key
     *
     * @return array|string|bool
     */
    public function getMetadata($key = null)
    {
        $result = $this->service->getMetadataContainer($this->getName());
        if (!empty($result) && is_array($result)) {
            if (empty($key)) {
                return $result['metadata'];
            } else {
                if (isset ($result['metadata'][$key])) {
                    return $result['metadata'][$key];
                }
            }
        }

        return false;
    }

    /**
     * Get the information of the container (total of objects, total size)
     *
     * @return array|bool
     */
    public function getInfo()
    {
        $result = $this->service->getMetadataContainer($this->getName());
        if (!empty($result) && is_array($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get all the object of the container
     *
     * @return Zend_Service_Rackspace_Files_ObjectList
     */
    public function getObjects()
    {
        return $this->service->getObjects($this->getName());
    }

    /**
     * Get an object of the container
     *
     * @param string $name
     * @param array  $headers
     *
     * @return Zend_Service_Rackspace_Files_Object|bool
     */
    public function getObject($name, $headers = array())
    {
        return $this->service->getObject($this->getName(), $name, $headers);
    }

    /**
     * Add an object in the container
     *
     * @param string $name
     * @param string $file the content of the object
     * @param array  $metadata
     *
     * @return bool
     */
    public function addObject($name, $file, $metadata = array())
    {
        return $this->service->storeObject(
            $this->getName(), $name, $file, $metadata
        );
    }

    /**
     * Delete an object in the container
     *
     * @param string $obj
     *
     * @return bool
     */
    public function deleteObject($obj)
    {
        return $this->service->deleteObject($this->getName(), $obj);
    }

    /**
     * Copy an object to another container
     *
     * @param string $obj_source
     * @param string $container_dest
     * @param string $obj_dest
     * @param array  $metadata
     * @param string $content_type
     *
     * @return bool
     */
    public function copyObject(
        $obj_source, $container_dest, $obj_dest, $metadata = array(),
        $content_type = null
    )
    {
        return $this->service->copyObject(
            $this->getName(),
            $obj_source,
            $container_dest,
            $obj_dest,
            $metadata,
            $content_type
        );
    }

    /**
     * Get the metadata of an object in the container
     *
     * @param string $object
     *
     * @return array
     */
    public function getMetadataObject($object)
    {
        return $this->service->getMetadataObject($this->getName(), $object);
    }

    /**
     * Set the metadata of an object in the container
     *
     * @param string $object
     * @param array  $metadata
     *
     * @return bool
     */
    public function setMetadataObject($object, $metadata = array())
    {
        return $this->service->setMetadataObject(
            $this->getName(), $object, $metadata
        );
    }

    /**
     * Enable the CDN for the container
     *
     * @param integer $ttl
     *
     * @return array|bool
     */
    public function enableCdn($ttl = Zend_Service_Rackspace_Files::CDN_TTL_MIN)
    {
        return $this->service->enableCdnContainer($this->getName(), $ttl);
    }

    /**
     * Disable the CDN for the container
     *
     * @return bool
     */
    public function disableCdn()
    {
        $result =
            $this->service->updateCdnContainer($this->getName(), null, false);

        return ($result !== false);
    }

    /**
     * Change the TTL for the CDN container
     *
     * @param integer $ttl
     *
     * @return bool
     */
    public function changeTtlCdn($ttl)
    {
        $result = $this->service->updateCdnContainer($this->getName(), $ttl);

        return ($result !== false);
    }

    /**
     * Enable the log retention for the CDN
     *
     * @return bool
     */
    public function enableLogCdn()
    {
        $result = $this->service->updateCdnContainer(
            $this->getName(), null, null, true
        );

        return ($result !== false);
    }

    /**
     * Disable the log retention for the CDN
     *
     * @return bool
     */
    public function disableLogCdn()
    {
        $result = $this->service->updateCdnContainer(
            $this->getName(), null, null, false
        );

        return ($result !== false);
    }

    /**
     * Get the CDN information
     *
     * @return array|bool
     */
    public function getCdnInfo()
    {
        return $this->service->getInfoCdnContainer($this->getName());
    }
}
