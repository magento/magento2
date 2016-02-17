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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Framework_Registry_Interface
 */
#require_once 'Zend/Tool/Framework/Registry/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Registry implements Zend_Tool_Framework_Registry_Interface
{
    /**
     * @var Zend_Tool_Framework_Loader_Abstract
     */
    protected $_loader = null;

    /**
     * @var Zend_Tool_Framework_Client_Abstract
     */
    protected $_client = null;

    /**
     * @var Zend_Tool_Framework_Client_Config
     */
    protected $_config = null;

    /**
     * @var Zend_Tool_Framework_Client_Storage
     */
    protected $_storage = null;

    /**
     * @var Zend_Tool_Framework_Action_Repository
     */
    protected $_actionRepository = null;

    /**
     * @var Zend_Tool_Framework_Provider_Repository
     */
    protected $_providerRepository = null;

    /**
     * @var Zend_Tool_Framework_Manifest_Repository
     */
    protected $_manifestRepository = null;

    /**
     * @var Zend_Tool_Framework_Client_Request
     */
    protected $_request = null;

    /**
     * @var Zend_Tool_Framework_Client_Response
     */
    protected $_response = null;

    /**
     * reset() - Reset all internal properties
     *
     */
    public function reset()
    {
        unset($this->_client);
        unset($this->_loader);
        unset($this->_actionRepository);
        unset($this->_providerRepository);
        unset($this->_request);
        unset($this->_response);
    }

//    public function __construct()
//    {
//        // no instantiation from outside
//    }

    /**
     * Enter description here...
     *
     * @param Zend_Tool_Framework_Client_Abstract $client
     * @return Zend_Tool_Framework_Registry
     */
    public function setClient(Zend_Tool_Framework_Client_Abstract $client)
    {
        $this->_client = $client;
        if ($this->isObjectRegistryEnablable($this->_client)) {
            $this->enableRegistryOnObject($this->_client);
        }
        return $this;
    }

    /**
     * getClient() return the client in the registry
     *
     * @return Zend_Tool_Framework_Client_Abstract
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * setConfig()
     *
     * @param Zend_Tool_Framework_Client_Config $config
     * @return Zend_Tool_Framework_Registry
     */
    public function setConfig(Zend_Tool_Framework_Client_Config $config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * getConfig()
     *
     * @return Zend_Tool_Framework_Client_Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            #require_once 'Zend/Tool/Framework/Client/Config.php';
            $this->setConfig(new Zend_Tool_Framework_Client_Config());
        }

        return $this->_config;
    }

    /**
     * setStorage()
     *
     * @param Zend_Tool_Framework_Client_Storage $storage
     * @return Zend_Tool_Framework_Registry
     */
    public function setStorage(Zend_Tool_Framework_Client_Storage $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * getConfig()
     *
     * @return Zend_Tool_Framework_Client_Storage
     */
    public function getStorage()
    {
        if ($this->_storage === null) {
            #require_once 'Zend/Tool/Framework/Client/Storage.php';
            $this->setStorage(new Zend_Tool_Framework_Client_Storage());
        }

        return $this->_storage;
    }

    /**
     * setLoader()
     *
     * @param Zend_Tool_Framework_Loader_Interface $loader
     * @return Zend_Tool_Framework_Registry
     */
    public function setLoader(Zend_Tool_Framework_Loader_Interface $loader)
    {
        $this->_loader = $loader;
        if ($this->isObjectRegistryEnablable($this->_loader)) {
            $this->enableRegistryOnObject($this->_loader);
        }
        return $this;
    }

    /**
     * getLoader()
     *
     * @return Zend_Tool_Framework_Loader_Abstract
     */
    public function getLoader()
    {
        if ($this->_loader === null) {
            #require_once 'Zend/Tool/Framework/Loader/IncludePathLoader.php';
            $this->setLoader(new Zend_Tool_Framework_Loader_IncludePathLoader());
        }

        return $this->_loader;
    }

    /**
     * setActionRepository()
     *
     * @param Zend_Tool_Framework_Action_Repository $actionRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setActionRepository(Zend_Tool_Framework_Action_Repository $actionRepository)
    {
        $this->_actionRepository = $actionRepository;
        if ($this->isObjectRegistryEnablable($this->_actionRepository)) {
            $this->enableRegistryOnObject($this->_actionRepository);
        }
        return $this;
    }

    /**
     * getActionRepository()
     *
     * @return Zend_Tool_Framework_Action_Repository
     */
    public function getActionRepository()
    {
        if ($this->_actionRepository == null) {
            #require_once 'Zend/Tool/Framework/Action/Repository.php';
            $this->setActionRepository(new Zend_Tool_Framework_Action_Repository());
        }

        return $this->_actionRepository;
    }

    /**
     * setProviderRepository()
     *
     * @param Zend_Tool_Framework_Provider_Repository $providerRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setProviderRepository(Zend_Tool_Framework_Provider_Repository $providerRepository)
    {
        $this->_providerRepository = $providerRepository;
        if ($this->isObjectRegistryEnablable($this->_providerRepository)) {
            $this->enableRegistryOnObject($this->_providerRepository);
        }
        return $this;
    }

    /**
     * getProviderRepository()
     *
     * @return Zend_Tool_Framework_Provider_Repository
     */
    public function getProviderRepository()
    {
        if ($this->_providerRepository == null) {
            #require_once 'Zend/Tool/Framework/Provider/Repository.php';
            $this->setProviderRepository(new Zend_Tool_Framework_Provider_Repository());
        }

        return $this->_providerRepository;
    }

    /**
     * setManifestRepository()
     *
     * @param Zend_Tool_Framework_Manifest_Repository $manifestRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setManifestRepository(Zend_Tool_Framework_Manifest_Repository $manifestRepository)
    {
        $this->_manifestRepository = $manifestRepository;
        if ($this->isObjectRegistryEnablable($this->_manifestRepository)) {
            $this->enableRegistryOnObject($this->_manifestRepository);
        }
        return $this;
    }

    /**
     * getManifestRepository()
     *
     * @return Zend_Tool_Framework_Manifest_Repository
     */
    public function getManifestRepository()
    {
        if ($this->_manifestRepository == null) {
            #require_once 'Zend/Tool/Framework/Manifest/Repository.php';
            $this->setManifestRepository(new Zend_Tool_Framework_Manifest_Repository());
        }

        return $this->_manifestRepository;
    }

    /**
     * setRequest()
     *
     * @param Zend_Tool_Framework_Client_Request $request
     * @return Zend_Tool_Framework_Registry
     */
    public function setRequest(Zend_Tool_Framework_Client_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * getRequest()
     *
     * @return Zend_Tool_Framework_Client_Request
     */
    public function getRequest()
    {
        if ($this->_request == null) {
            #require_once 'Zend/Tool/Framework/Client/Request.php';
            $this->setRequest(new Zend_Tool_Framework_Client_Request());
        }

        return $this->_request;
    }

    /**
     * setResponse()
     *
     * @param Zend_Tool_Framework_Client_Response $response
     * @return Zend_Tool_Framework_Registry
     */
    public function setResponse(Zend_Tool_Framework_Client_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * getResponse()
     *
     * @return Zend_Tool_Framework_Client_Response
     */
    public function getResponse()
    {
        if ($this->_response == null) {
            #require_once 'Zend/Tool/Framework/Client/Response.php';
            $this->setResponse(new Zend_Tool_Framework_Client_Response());
        }

        return $this->_response;
    }

    /**
     * __get() - Get a property via property call $registry->foo
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . $name)) {
            return $this->{'get' . $name}();
        } else {
            #require_once 'Zend/Tool/Framework/Registry/Exception.php';
            throw new Zend_Tool_Framework_Registry_Exception('Property ' . $name . ' was not located in this registry.');
        }
    }

    /**
     * __set() - Set a property via the magic set $registry->foo = 'foo'
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . $name)) {
            $this->{'set' . $name}($value);
            return;
        } else {
            #require_once 'Zend/Tool/Framework/Registry/Exception.php';
            throw new Zend_Tool_Framework_Registry_Exception('Property ' . $name . ' was not located in this registry.');
        }
    }

    /**
     * isObjectRegistryEnablable() - Check whether an object is registry enablable
     *
     * @param object $object
     * @return bool
     */
    public function isObjectRegistryEnablable($object)
    {
        if (!is_object($object)) {
            #require_once 'Zend/Tool/Framework/Registry/Exception.php';
            throw new Zend_Tool_Framework_Registry_Exception('isObjectRegistryEnablable() expects an object.');
        }

        return ($object instanceof Zend_Tool_Framework_Registry_EnabledInterface);
    }

    /**
     * enableRegistryOnObject() - make an object registry enabled
     *
     * @param object $object
     * @return Zend_Tool_Framework_Registry
     */
    public function enableRegistryOnObject($object)
    {
        if (!$this->isObjectRegistryEnablable($object)) {
            #require_once 'Zend/Tool/Framework/Registry/Exception.php';
            throw new Zend_Tool_Framework_Registry_Exception('Object provided is not registry enablable, check first with Zend_Tool_Framework_Registry::isObjectRegistryEnablable()');
        }

        $object->setRegistry($this);
        return $this;
    }

}
