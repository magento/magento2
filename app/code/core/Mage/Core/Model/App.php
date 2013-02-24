<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application model
 *
 * Application should have: areas, store, locale, translator, design package
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_App implements Mage_Core_Model_AppInterface
{
    /**
     * Application loaded areas array
     *
     * @var array
     */
    protected $_areas = array();

    /**
     * Application location object
     *
     * @var Mage_Core_Model_Locale
     */
    protected $_locale;

    /**
     * Application configuration object
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Application front controller
     *
     * @var Mage_Core_Controller_FrontInterface
     */
    protected $_frontController;

    /**
     * Flag to identify whether front controller is initialized
     *
     * @var bool
     */
    protected $_isFrontControllerInitialized = false;

    /**
     * Cache object
     *
     * @var Mage_Core_Model_Cache
     */
    protected $_cache;

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Response object
     *
     * @var Zend_Controller_Response_Http
     */
    protected $_response;

    /**
     * Use session in URL flag
     *
     * @see Mage_Core_Model_Url
     * @var bool
     */
    protected $_useSessionInUrl = true;

    /**
     * Use session var instead of SID for session in URL
     *
     * @var bool
     */
    protected $_useSessionVar = false;

    /**
     * Object manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Data base updater object
     *
     * @var Mage_Core_Model_Db_UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * Store list manager
     * 
     * @var Mage_Core_Model_StoreManager
     */
    protected $_storeManager;

    /**
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Cache $cache
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Db_UpdaterInterface $dbUpdater
     * @param Mage_Core_Model_StoreManager $storeManager
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_App_State $appState
     */
    public function __construct(
        Mage_Core_Model_Config $config,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Cache $cache,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Db_UpdaterInterface $dbUpdater,
        Mage_Core_Model_StoreManager $storeManager,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_App_State $appState
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_dbUpdater = $dbUpdater;
        $this->_frontController = $frontController;
        $this->_appState = $appState;
        $this->_eventManager = $eventManager;
    }

    /**
     * Run application. Run process responsible for request processing and sending response.
     *
     * @return Mage_Core_Model_App
     */
    public function run()
    {
        Magento_Profiler::start('init');

        $this->_initRequest();
        $this->_dbUpdater->updateData();

        $controllerFront = $this->getFrontController();
        Magento_Profiler::stop('init');

        $controllerFront->dispatch();

        return $this;
    }

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @throws Magento_Exception
     */
    public function requireInstalledInstance()
    {
        if (false == $this->_appState->isInstalled()) {
            throw new Magento_Exception('Application is not installed yet, please complete the installation first.');
        }
    }

    /**
     * Init request object
     *
     * @return Mage_Core_Model_App
     */
    protected function _initRequest()
    {
        $this->getRequest()->setPathInfo();
        return $this;
    }

    /**
     * Retrieve cookie object
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return $this->_objectManager->get('Mage_Core_Model_Cookie');
    }

    /**
     * Initialize application front controller
     *
     * @return Mage_Core_Model_App
     */
    protected function _initFrontController()
    {
        $this->_frontController = $this->_getFrontControllerByCurrentArea();
        Magento_Profiler::start('init_front_controller');
        $this->_frontController->init();
        Magento_Profiler::stop('init_front_controller');
        return $this;
    }

    /**
     * Instantiate proper front controller instance depending on current area
     *
     * @return Mage_Core_Controller_FrontInterface
     */
    protected function _getFrontControllerByCurrentArea()
    {
        /**
         * TODO: Temporary implementation for API. Must be reconsidered during implementation
         * TODO: of ability to set different front controllers in different area.
         * TODO: See also related changes in Mage_Core_Model_Config.
         */
        // TODO: Assure that everything work fine work in areas without routers (e.g. URL generation)
        /** Default front controller class */
        $frontControllerClass = 'Mage_Core_Controller_Varien_Front';
        $pathParts = explode('/', trim($this->getRequest()->getPathInfo(), '/'));
        if ($pathParts) {
            /** If area front name is used it is expected to be set on the first place in path info */
            $frontName = reset($pathParts);
            foreach ($this->getConfig()->getAreas() as $areaCode => $areaInfo) {
                if (isset($areaInfo['front_controller'])
                    && isset($areaInfo['frontName']) && ($frontName == $areaInfo['frontName'])
                ) {
                    $this->getConfig()->setCurrentAreaCode($areaCode);
                    $frontControllerClass = $areaInfo['front_controller'];
                    break;
                }
            }
        }
        return $this->_objectManager->create($frontControllerClass);
    }

    /**
     * Re-declare custom error handler
     *
     * @param   string $handler
     * @return  Mage_Core_Model_App
     */
    public function setErrorHandler($handler)
    {
        set_error_handler($handler);
        return $this;
    }

    /**
     * Loading application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_App
     */
    public function loadArea($code)
    {
        $this->getArea($code)->load();
        return $this;
    }

    /**
     * Loading part of area data
     *
     * @param   string $area
     * @param   string $part
     * @return  Mage_Core_Model_App
     */
    public function loadAreaPart($area, $part)
    {
        $this->getArea($area)->load($part);
        return $this;
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_App_Area
     */
    public function getArea($code)
    {
        if (!isset($this->_areas[$code])) {
            $this->_areas[$code] = $this->_objectManager->create(
                'Mage_Core_Model_App_Area',
                array('areaCode' => $code)
            );
        }
        return $this->_areas[$code];
    }

    public function getDistroLocaleCode()
    {
        return self::DISTRO_LOCALE_CODE;
    }

    /**
     * Retrieve application locale object
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = $this->_objectManager->get('Mage_Core_Model_Locale');
        }
        return $this->_locale;
    }

    /**
     * Retrieve layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_objectManager->get('Mage_Core_Model_Layout');
    }

    /**
     * Retrieve helper object
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper($name)
    {
        return Mage::helper($name);
    }

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return (string) $this->_config->getNode('default/' . Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve configuration object
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Retrieve front controller object
     *
     * @return Mage_Core_Controller_Varien_Front
     */
    public function getFrontController()
    {
        if (!$this->_isFrontControllerInitialized) {
            $this->_initFrontController();
            $this->_isFrontControllerInitialized = true;
        }
        return $this->_frontController;
    }

    /**
     * Get core cache model
     *
     * @return Mage_Core_Model_Cache
     */
    public function getCacheInstance()
    {
        return $this->_cache;
    }

    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_cache->getFrontend();
    }

    /**
     * Loading cache data
     *
     * @param   string $id
     * @return  mixed
     */
    public function loadCache($id)
    {
        return $this->_cache->load($id);
    }

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $id
     * @param array $tags
     * @param bool $lifeTime
     * @return Mage_Core_Model_App
     */
    public function saveCache($data, $id, $tags = array(), $lifeTime = false)
    {
        $this->_cache->save($data, $id, $tags, $lifeTime);
        return $this;
    }

    /**
     * Remove cache
     *
     * @param   string $id
     * @return  Mage_Core_Model_App
     */
    public function removeCache($id)
    {
        $this->_cache->remove($id);
        return $this;
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  Mage_Core_Model_App
     */
    public function cleanCache($tags = array())
    {
        $this->_cache->clean($tags);
        $this->_eventManager->dispatch('application_clean_cache', array('tags' => $tags));
        return $this;
    }

    /**
     * Check whether to use cache for specific component
     *
     * @param null|string $type
     * @return boolean
     */
    public function useCache($type = null)
    {
        return $this->_cache->canUse($type);
    }

    /**
     * Save cache usage settings
     *
     * @param array $data
     * @return Mage_Core_Model_App
     */
    public function saveUseCache($data)
    {
        $this->_cache->saveOptions($data);
        return $this;
    }

    /**
     * Deletes all session files
     *
     * @return Mage_Core_Model_App
     */
    public function cleanAllSessions()
    {
        if (session_module_name() == 'files') {
            /** @var Magento_Filesystem $filesystem */
            $filesystem = $this->_objectManager->create('Magento_Filesystem');
            $filesystem->delete(session_save_path());
        }
        return $this;
    }

    /**
     * Retrieve request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = $this->_objectManager->get('Mage_Core_Controller_Request_Http');
        }
        return $this->_request;
    }

    /**
     * Request setter
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_Core_Model_App
     */
    public function setRequest(Mage_Core_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Retrieve response object
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_objectManager->get('Mage_Core_Controller_Response_Http');
            $this->_response->headersSentThrowsException = Mage::$headersSentThrowsException;
            $this->_response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        }
        return $this->_response;
    }

    /**
     * Response setter
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @return Mage_Core_Model_App
     */
    public function setResponse(Mage_Core_Controller_Response_Http $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return Mage_Core_Model_App
     */
    public function setUseSessionVar($var)
    {
        $this->_useSessionVar = (bool)$var;
        return $this;
    }

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     */
    public function getUseSessionVar()
    {
        return $this->_useSessionVar;
    }

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return Mage_Core_Model_App
     */
    public function setUseSessionInUrl($flag = true)
    {
        $this->_useSessionInUrl = (bool)$flag;
        return $this;
    }

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     */
    public function getUseSessionInUrl()
    {
        return $this->_useSessionInUrl;
    }

    /**
     * Check if developer mode is enabled
     * 
     * @return bool
     */
    public function isDeveloperMode()
    {
        return $this->_appState->isDeveloperMode();
    }

    /**
     * Retrieve application store object without Store_Exception
     *
     * @param string|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     *
     * @deprecated use Mage_Core_Model_StoreManager::getSafeStore()
     */
    public function getSafeStore($id = null)
    {
        return $this->_storeManager->getSafeStore($id);
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     *
     * @deprecated use Mage_Core_Model_StoreManager::setIsSingleStoreModeAllowed()
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->_storeManager->setIsSingleStoreModeAllowed($value);
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     *
     * @deprecated use Mage_Core_Model_StoreManager::hasSingleStore()
     */
    public function hasSingleStore()
    {
        return $this->_storeManager->hasSingleStore();
    }

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     *
     * @deprecated use Mage_Core_Model_StoreManager::isSingleStoreMode()
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     *
     * @deprecated use Mage_Core_Model_StoreManager::throwStoreException()
     */
    public function throwStoreException()
    {
        $this->_storeManager->throwStoreException();
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     *
     * @deprecated use Mage_Core_Model_StoreManager::getStore()
     */
    public function getStore($id = null)
    {
        return $this->_storeManager->getStore($id);
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Mage_Core_Model_Store[]
     *
     * @deprecated use Mage_Core_Model_StoreManager::getStores()
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getStores($withDefault, $codeKey);
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     * @return Mage_Core_Model_Website
     * @throws Mage_Core_Exception
     *
     * @deprecated use Mage_Core_Model_StoreManager::getWebsite()
     */
    public function getWebsite($id = null)
    {
        return $this->_storeManager->getWebsite($id);
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return Mage_Core_Model_Website[]
     *
     * @deprecated use Mage_Core_Model_StoreManager::getWebsites()
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getWebsites($withDefault, $codeKey);
    }

    /**
     * Reinitialize store list
     *
     * @deprecated use Mage_Core_Model_StoreManager::reinitStores()
     */
    public function reinitStores()
    {
        $this->_storeManager->reinitStores();
    }

    /**
     * Set current default store
     *
     * @param string $store
     *
     * @deprecated use Mage_Core_Model_StoreManager::setCurrentStore()
     */
    public function setCurrentStore($store)
    {
        $this->_storeManager->setCurrentStore($store);
    }

    /**
     * Get current store code
     *
     * @return string
     *
     * @deprecated use Mage_Core_Model_StoreManager::getCurrentStore()
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getCurrentStore();
    }


    /**
     * Retrieve default store for default group and website
     *
     * @return Mage_Core_Model_Store
     *
     * @deprecated use Mage_Core_Model_StoreManager::getDefaultStoreView()
     */
    public function getDefaultStoreView()
    {
        return $this->_storeManager->getDefaultStoreView();
    }

    /**
     * Retrieve application store group object
     *
     * @param null|Mage_Core_Model_Store_Group|string $id
     * @return Mage_Core_Model_Store_Group
     * @throws Mage_Core_Exception
     *
     * @deprecated use Mage_Core_Model_StoreManager::getGroup()
     */
    public function getGroup($id = null)
    {
        return $this->_storeManager->getGroup($id);
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Mage_Core_Model_Store_Group[]
     *
     * @deprecated use Mage_Core_Model_StoreManager::getGroups()
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getGroups($withDefault, $codeKey);
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     *
     * @deprecated use Mage_Core_Model_StoreManager::clearWebsiteCache()
     */
    public function clearWebsiteCache($id = null)
    {
        $this->_storeManager->clearWebsiteCache($id);
    }

    /**
     * Get either default or any store view
     *
     * @return Mage_Core_Model_Store|null
     *
     * @deprecated use Mage_Core_Model_StoreManager::getAnyStoreView()
     */
    public function getAnyStoreView()
    {
        return $this->_storeManager->getAnyStoreView();
    }
}
