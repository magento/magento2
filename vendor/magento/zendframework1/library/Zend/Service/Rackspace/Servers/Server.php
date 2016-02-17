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
 * @subpackage Servers
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Service/Rackspace/Servers.php';

class Zend_Service_Rackspace_Servers_Server
{
    const ERROR_PARAM_CONSTRUCT = 'You must pass a Zend_Service_Rackspace_Servers object and an array';
    const ERROR_PARAM_NO_NAME   = 'You must pass the server\'s name in the array (name)';
    const ERROR_PARAM_NO_ID     = 'You must pass the server\'s id in the array (id)';
    /**
     * Server's name
     *
     * @var string
     */
    protected $name;
    /**
     * Server's id
     *
     * @var string
     */
    protected $id;
    /**
     * Image id of the server
     *
     * @var string
     */
    protected $imageId;
    /**
     * Flavor id of the server
     *
     * @var string
     */
    protected $flavorId;
    /**
     * Host id
     *
     * @var string
     */
    protected $hostId;
    /**
     * Server's status
     *
     * @var string
     */
    protected $status;
    /**
     * Progress of the status
     *
     * @var integer
     */
    protected $progress;
    /**
     * Admin password, generated on a new server
     *
     * @var string
     */
    protected $adminPass;
    /**
     * Public and private IP addresses
     *
     * @var array
     */
    protected $addresses = array();
    /**
     * @var array
     */
    protected $metadata = array();
    /**
     * The service that has created the server object
     *
     * @var Zend_Service_Rackspace_Servers
     */
    protected $service;
    /**
     * Constructor
     *
     * @param  Zend_Service_Rackspace_Servers $service
     * @param  array $data
     * @return void
     */
    public function __construct($service, $data)
    {
        if (!($service instanceof Zend_Service_Rackspace_Servers) || !is_array($data)) {
            #require_once 'Zend/Service/Rackspace/Servers/Exception.php';
            throw new Zend_Service_Rackspace_Servers_Exception(self::ERROR_PARAM_CONSTRUCT);
        }
        if (!array_key_exists('name', $data)) {
            #require_once 'Zend/Service/Rackspace/Servers/Exception.php';
            throw new Zend_Service_Rackspace_Servers_Exception(self::ERROR_PARAM_NO_NAME);
        }
        if (!array_key_exists('id', $data)) {
            #require_once 'Zend/Service/Rackspace/Servers/Exception.php';
            throw new Zend_Service_Rackspace_Servers_Exception(self::ERROR_PARAM_NO_ID);
        }
        $this->service = $service;
        $this->name = $data['name'];
        $this->id = $data['id'];
        if (isset($data['imageId'])) {
            $this->imageId= $data['imageId'];
        }
        if (isset($data['flavorId'])) {
            $this->flavorId= $data['flavorId'];
        }
        if (isset($data['hostId'])) {
            $this->hostId= $data['hostId'];
        }
        if (isset($data['status'])) {
            $this->status= $data['status'];
        }
        if (isset($data['progress'])) {
            $this->progress= $data['progress'];
        }
        if (isset($data['adminPass'])) {
            $this->adminPass= $data['adminPass'];
        }
        if (isset($data['addresses']) && is_array($data['addresses'])) {
            $this->addresses= $data['addresses'];
        }
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $this->metadata= $data['metadata'];
        }
    }
    /**
     * Get the name of the server
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Get the server's id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Get the server's image Id
     *
     * @return string
     */
    public function getImageId()
    {
        return $this->imageId;
    }
    /**
     * Get the server's flavor Id
     *
     * @return string
     */
    public function getFlavorId()
    {
        return $this->flavorId;
    }
    /**
     * Get the server's host Id
     *
     * @return string
     */
    public function getHostId()
    {
        return $this->hostId;
    }
    /**
     * Ge the server's admin password
     *
     * @return string
     */
    public function getAdminPass()
    {
        return $this->adminPass;
    }
    /**
     * Get the server's status
     *
     * @return string|boolean
     */
    public function getStatus()
    {
        $data= $this->service->getServer($this->id);
        if ($data!==false) {
            $data= $data->toArray();
            $this->status= $data['status'];
            return $this->status;
        }
        return false;
    }
    /**
     * Get the progress's status
     *
     * @return integer|boolean
     */
    public function getProgress()
    {
        $data= $this->service->getServer($this->id);
        if ($data!==false) {
            $data= $data->toArray();
            $this->progress= $data['progress'];
            return $this->progress;
        }
        return false;
    }
    /**
     * Get the private IPs
     *
     * @return array|boolean
     */
    public function getPrivateIp()
    {
        if (isset($this->addresses['private'])) {
            return $this->addresses['private'];
        }
        return false;
    }
    /**
     * Get the public IPs
     *
     * @return array|boolean
     */
    public function getPublicIp()
    {
        if (isset($this->addresses['public'])) {
            return $this->addresses['public'];
        }
        return false;
    }
    /**
     * Get the metadata of the container
     *
     * If $key is empty return the array of metadata
     *
     * @param string $key
     * @return array|string
     */
    public function getMetadata($key=null)
    {
        if (!empty($key) && isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }
        return $this->metadata;
    }
    /**
     * Change the name of the server
     *
     * @param string $name
     * @return boolean
     */
    public function changeName($name)
    {
        $result= $this->service->changeServerName($this->id, $name);
        if ($result!==false) {
            $this->name= $name;
            return true;
        }
        return false;
    }
    /**
     * Change the admin password of the server
     *
     * @param string $password
     * @return boolean
     */
    public function changePassword($password)
    {
        $result=  $this->service->changeServerPassword($this->id, $password);
        if ($result!==false) {
            $this->adminPass= $password;
            return true;
        }
        return false;
    }
    /**
     * Reboot the server
     *
     * @return boolean
     */
    public function reboot($hard=false)
    {
        return $this->service->rebootServer($this->id,$hard);
    }
    /**
     * To Array
     *
     * @return array
     */
    public function toArray()
    {
        return array (
            'name'      => $this->name,
            'id'        => $this->id,
            'imageId'   => $this->imageId,
            'flavorId'  => $this->flavorId,
            'hostId'    => $this->hostId,
            'status'    => $this->status,
            'progress'  => $this->progress,
            'adminPass' => $this->adminPass,
            'addresses' => $this->addresses,
            'metadata'  => $this->metadata
        );
    }
}
