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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application model
 *
 * Application should have: areas, store, locale, translator, design package
 */
namespace Magento\Core\Model;

class App implements \Magento\Core\Model\AppInterface
{
    /**#@+
     * Product edition labels
     */
    const EDITION_COMMUNITY    = 'Community';
    const EDITION_ENTERPRISE   = 'Enterprise';
    /**#@-*/

    /**
     * Current Magento edition.
     *
     * @var string
     * @static
     */
    protected $_currentEdition = self::EDITION_COMMUNITY;

    /**
     * Magento version
     */
    const VERSION = '2.0.0.0-dev49';

    /**
     * Custom application dirs
     */
    const PARAM_APP_DIRS = 'app_dirs';

    /**
     * Custom application uris
     */
    const PARAM_APP_URIS = 'app_uris';

    /**
     * Custom local configuration file name
     */
    const PARAM_CUSTOM_LOCAL_FILE = 'custom_local_xml';

    /**
     * Custom local configuration
     */
    const PARAM_CUSTOM_LOCAL_CONFIG = 'custom_local_config';

    /**
     * Application run code
     */
    const PARAM_MODE = 'MAGE_MODE';

    /**
     * Application run code
     */
    const PARAM_RUN_CODE = 'MAGE_RUN_CODE';

    /**
     * Application run type (store|website)
     */
    const PARAM_RUN_TYPE = 'MAGE_RUN_TYPE';

    /**
     * Disallow cache
     */
    const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Allowed modules
     */
    const PARAM_ALLOWED_MODULES = 'allowed_modules';

    /**
     * Caching params
     */
    const PARAM_CACHE_OPTIONS = 'cache_options';

    /**
     * Application loaded areas array
     *
     * @var array
     */
    protected $_areas = array();

    /**
     * Application location object
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Application configuration object
     *
     * @var \Magento\Core\Model\Config
     */
    protected $_config;

    /**
     * Application front controller
     *
     * @var \Magento\App\FrontControllerInterface
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
     * @var \Magento\Core\Model\CacheInterface
     */
    protected $_cache;

    /**
     * Request object
     *
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * Response object
     *
     * @var \Magento\App\ResponseInterface
     */
    protected $_response;

    /**
     * Use session in URL flag
     *
     * @see \Magento\Core\Model\Url
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
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Data base updater object
     *
     * @var \Magento\App\UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * Store list manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Config\Scope
     */
    protected $_configScope;

    /**
     * @param Config $config
     * @param CacheInterface $cache
     * @param \Magento\ObjectManager $objectManager
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\App\State $appState
     * @param \Magento\Config\Scope $configScope
     * @param \Magento\App\FrontControllerInterface $frontController
     */
    public function __construct(
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\CacheInterface $cache,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\App\State $appState,
        \Magento\Config\Scope $configScope,
        \Magento\App\FrontControllerInterface $frontController
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_appState = $appState;
        $this->_eventManager = $eventManager;
        $this->_configScope = $configScope;
        $this->_frontController = $frontController;
    }

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @throws \Magento\Exception
     */
    public function requireInstalledInstance()
    {
        if (false == $this->_appState->isInstalled()) {
            throw new \Magento\Exception('Application is not installed yet, please complete the installation first.');
        }
    }

    /**
     * Retrieve cookie object
     *
     * @return \Magento\Core\Model\Cookie
     */
    public function getCookie()
    {
        return $this->_objectManager->get('Magento\Core\Model\Cookie');
    }

    /**
     * Re-declare custom error handler
     *
     * @param   string $handler
     * @return  \Magento\Core\Model\App
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
     * @return  \Magento\Core\Model\App
     */
    public function loadArea($code)
    {
        $this->_configScope->setCurrentScope($code);
        $this->getArea($code)->load();
        return $this;
    }

    /**
     * Loading part of area data
     *
     * @param   string $area
     * @param   string $part
     * @return  \Magento\Core\Model\App
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
     * @return  \Magento\Core\Model\App\Area
     */
    public function getArea($code)
    {
        if (!isset($this->_areas[$code])) {
            $this->_areas[$code] = $this->_objectManager->create(
                'Magento\Core\Model\App\Area',
                array('areaCode' => $code)
            );
        }
        return $this->_areas[$code];
    }

    /**
     * Get distro locale code
     *
     * @return string
     */
    public function getDistroLocaleCode()
    {
        return self::DISTRO_LOCALE_CODE;
    }

    /**
     * Retrieve application locale object
     *
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = $this->_objectManager->get('Magento\Core\Model\LocaleInterface');
        }
        return $this->_locale;
    }

    /**
     * Retrieve layout object
     *
     * @return \Magento\View\LayoutInterface
     */
    public function getLayout()
    {
        return $this->_objectManager->get('Magento\View\LayoutInterface');
    }

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_config->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE, 'default');
    }

    /**
     * Retrieve configuration object
     *
     * @return \Magento\Core\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Retrieve front controller object
     *
     * @return \Magento\App\FrontController
     */
    public function getFrontController()
    {
        return $this->_frontController;
    }

    /**
     * Get core cache model
     *
     * @return \Magento\Core\Model\CacheInterface
     */
    public function getCacheInstance()
    {
        return $this->_cache;
    }

    /**
     * Retrieve cache object
     *
     * @return \Magento\Cache\FrontendInterface
     */
    public function getCache()
    {
        return $this->_cache->getFrontend();
    }

    /**
     * Loading cache data
     *
     * @param   string $cacheId
     * @return  mixed
     */
    public function loadCache($cacheId)
    {
        return $this->_cache->load($cacheId);
    }

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $cacheId
     * @param array $tags
     * @param bool $lifeTime
     * @return \Magento\Core\Model\App
     */
    public function saveCache($data, $cacheId, $tags = array(), $lifeTime = false)
    {
        $this->_cache->save($data, $cacheId, $tags, $lifeTime);
        return $this;
    }

    /**
     * Remove cache
     *
     * @param   string $cacheId
     * @return  \Magento\Core\Model\App
     */
    public function removeCache($cacheId)
    {
        $this->_cache->remove($cacheId);
        return $this;
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  \Magento\Core\Model\App
     */
    public function cleanCache($tags = array())
    {
        $this->_cache->clean($tags);
        return $this;
    }

    /**
     * Deletes all session files
     *
     * @return \Magento\Core\Model\App
     */
    public function cleanAllSessions()
    {
        if (session_module_name() == 'files') {
            /** @var \Magento\Filesystem $filesystem */
            $filesystem = $this->_objectManager->create('Magento\Filesystem');
            $filesystem->delete(session_save_path());
        }
        return $this;
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\App\RequestInterface
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = $this->_objectManager->get('Magento\App\RequestInterface');
        }
        return $this->_request;
    }

    /**
     * Request setter
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\Core\Model\App
     */
    public function setRequest(\Magento\App\RequestInterface $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\App\ResponseInterface
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_objectManager->get('Magento\App\ResponseInterface');
            $this->_response->setHeader('Content-Type', 'text/html; charset=UTF-8');
        }
        return $this->_response;
    }

    /**
     * Response setter
     *
     * @param \Magento\App\ResponseInterface $response
     * @return \Magento\Core\Model\App
     */
    public function setResponse(\Magento\App\ResponseInterface $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return \Magento\Core\Model\App
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
     * @return \Magento\Core\Model\App
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
        return $this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER;
    }

    /**
     * Retrieve application store object without Store_Exception
     *
     * @param string|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
     *
     * @deprecated use \Magento\Core\Model\StoreManagerInterface::getSafeStore()
     */
    public function getSafeStore($storeId = null)
    {
        return $this->_storeManager->getSafeStore($storeId);
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     *
     * @deprecated use \Magento\Core\Model\StoreManager::setIsSingleStoreModeAllowed()
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
     * @deprecated use \Magento\Core\Model\StoreManager::hasSingleStore()
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
     * @deprecated use \Magento\Core\Model\StoreManager::isSingleStoreMode()
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @throws \Magento\Core\Model\Store\Exception
     *
     * @deprecated use \Magento\Core\Model\StoreManager::throwStoreException()
     */
    public function throwStoreException()
    {
        $this->_storeManager->throwStoreException();
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
     * @throws \Magento\Core\Model\Store\Exception
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getStore()
     */
    public function getStore($storeId = null)
    {
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Core\Model\Store[]
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getStores()
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getStores($withDefault, $codeKey);
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     * @return \Magento\Core\Model\Website
     * @throws \Magento\Core\Exception
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getWebsite()
     */
    public function getWebsite($websiteId = null)
    {
        return $this->_storeManager->getWebsite($websiteId);
    }

    /**
     * Get loaded websites
     *
     * @param bool $withDefault
     * @param bool|string $codeKey
     * @return \Magento\Core\Model\Website[]
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getWebsites()
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getWebsites($withDefault, $codeKey);
    }

    /**
     * Reinitialize store list
     *
     * @deprecated use \Magento\Core\Model\StoreManager::reinitStores()
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
     * @deprecated use \Magento\Core\Model\StoreManager::setCurrentStore()
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
     * @deprecated use \Magento\Core\Model\StoreManager::getCurrentStore()
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getCurrentStore();
    }


    /**
     * Retrieve default store for default group and website
     *
     * @return \Magento\Core\Model\Store
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getDefaultStoreView()
     */
    public function getDefaultStoreView()
    {
        return $this->_storeManager->getDefaultStoreView();
    }

    /**
     * Retrieve application store group object
     *
     * @param null|\Magento\Core\Model\Store\Group|string $groupId
     * @return \Magento\Core\Model\Store\Group
     * @throws \Magento\Core\Exception
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getGroup()
     */
    public function getGroup($groupId = null)
    {
        return $this->_storeManager->getGroup($groupId);
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return \Magento\Core\Model\Store\Group[]
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getGroups()
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        return $this->_storeManager->getGroups($withDefault, $codeKey);
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     *
     * @deprecated use \Magento\Core\Model\StoreManager::clearWebsiteCache()
     */
    public function clearWebsiteCache($websiteId = null)
    {
        $this->_storeManager->clearWebsiteCache($websiteId);
    }

    /**
     * Get either default or any store view
     *
     * @return \Magento\Core\Model\Store|null
     *
     * @deprecated use \Magento\Core\Model\StoreManager::getAnyStoreView()
     */
    public function getAnyStoreView()
    {
        return $this->_storeManager->getAnyStoreView();
    }

    /**
     * Get current Magento edition
     *
     * @static
     * @return string
     */
    public function getEdition()
    {
        return $this->_currentEdition;
    }

    /**
     * Set edition
     *
     * @param string $edition
     */
    public function setEdition($edition)
    {
        $this->_currentEdition = $edition;
    }


    /**
     * Gets the current Magento version string
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return string
     */
    public function getVersion()
    {
        $info = $this->getVersionInfo();
        return trim("{$info['major']}.{$info['minor']}.{$info['revision']}"
            . ($info['patch'] != '' ? ".{$info['patch']}" : "")
            . "-{$info['stability']}{$info['number']}", '.-');
    }

    /**
     * Gets the detailed Magento version information
     * @link http://www.magentocommerce.com/blog/new-community-edition-release-process/
     *
     * @return array
     */
    public function getVersionInfo()
    {
        return array(
            'major'     => '2',
            'minor'     => '0',
            'revision'  => '0',
            'patch'     => '0',
            'stability' => 'dev',
            'number'    => '49',
        );
    }
}
