<?php
/**
 * Application proxy model
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_App_Proxy implements Mage_Core_Model_AppInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_App
     */
    protected $_app = null;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get application model
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        if (null === $this->_app) {
            $this->_app = $this->_objectManager->get('Mage_Core_Model_App');
        }
        
        return $this->_app;
    }

    /**
     * Run application. Run process responsible for request processing and sending response.
     *
     * @return Mage_Core_Model_AppInterface
     */
    public function run()
    {
        return $this->_getApp()->run();
    }

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @throws Magento_Exception
     */
    public function requireInstalledInstance()
    {
        $this->_getApp()->requireInstalledInstance();
    }

    /**
     * Retrieve cookie object
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie()
    {
        return $this->_getApp()->getCookie();
    }

    /**
     * Reinitialize stores
     *
     * @return void
     */
    public function reinitStores()
    {
        $this->_getApp()->reinitStores();
    }

    /**
     * Check if system is run in the single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_getApp()->isSingleStoreMode();
    }

    /**
     * Check if store has only one store view
     *
     * @return bool
     */
    public function hasSingleStore()
    {
        return $this->_getApp()->hasSingleStore();
    }

    /**
     * Set current default store
     *
     * @param string $store
     */
    public function setCurrentStore($store)
    {
        $this->_getApp()->setCurrentStore($store);
    }

    /**
     * Get current store code
     *
     * @return string
     */
    public function getCurrentStore()
    {
        return $this->_getApp()->getCurrentStore();
    }

    /**
     * Re-declare custom error handler
     *
     * @param   string $handler
     * @return  Mage_Core_Model_AppInterface
     */
    public function setErrorHandler($handler)
    {
        return $this->_getApp()->setErrorHandler($handler);
    }

    /**
     * Loading application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_AppInterface
     */
    public function loadArea($code)
    {
        return $this->_getApp()->loadArea($code);
    }

    /**
     * Loading part of area data
     *
     * @param   string $area
     * @param   string $part
     * @return  Mage_Core_Model_AppInterface
     */
    public function loadAreaPart($area, $part)
    {
        return $this->_getApp()->loadAreaPart($area, $part);
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_App_Area
     */
    public function getArea($code)
    {
        return $this->_getApp()->getArea($code);
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getStore($id = null)
    {
        return $this->_getApp()->getStore($id);
    }

    /**
     * Retrieve application store object without Store_Exception
     *
     * @param string|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store
     */
    public function getSafeStore($id = null)
    {
        return $this->_getApp()->getSafeStore($id);
    }

    /**
     * Retrieve stores array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return array
     */
    public function getStores($withDefault = false, $codeKey = false)
    {
        return $this->_getApp()->getStores($withDefault, $codeKey);
    }

    /**
     * Retrieve default store for default group and website
     *
     * @return Mage_Core_Model_Store
     */
    public function getDefaultStoreView()
    {
        return $this->_getApp()->getDefaultStoreView();
    }

    /**
     * Get distributive locale code
     *
     * @return string
     */
    public function getDistroLocaleCode()
    {
        return $this->_getApp()->getDistroLocaleCode();
    }

    /**
     * Retrieve application website object
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     * @return Mage_Core_Model_Website
     * @throws Mage_Core_Exception
     */
    public function getWebsite($id = null)
    {
        return $this->_getApp()->getWebsite($id);
    }

    /**
     * Get websites
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return array
     */
    public function getWebsites($withDefault = false, $codeKey = false)
    {
        return $this->_getApp()->getWebsites($withDefault, $codeKey);
    }

    /**
     * Retrieve application store group object
     *
     * @param null|Mage_Core_Model_Store_Group|string $id
     * @return Mage_Core_Model_Store_Group
     * @throws Mage_Core_Exception
     */
    public function getGroup($id = null)
    {
        return $this->_getApp()->getGroup($id);
    }

    /**
     * Retrieve application locale object
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale()
    {
        return $this->_getApp()->getLocale();
    }

    /**
     * Retrieve layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_getApp()->getLayout();
    }

    /**
     * Retrieve helper object
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper($name)
    {
        return $this->_getApp()->getHelper($name);
    }

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_getApp()->getBaseCurrencyCode();
    }

    /**
     * Retrieve configuration object
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfig()
    {
        return $this->_getApp()->getConfig();
    }

    /**
     * Retrieve front controller object
     *
     * @return Mage_Core_Controller_Varien_Front
     */
    public function getFrontController()
    {
        return $this->_getApp()->getFrontController();
    }

    /**
     * Get core cache model
     *
     * @return Mage_Core_Model_Cache
     */
    public function getCacheInstance()
    {
        return $this->_getApp()->getCacheInstance();
    }

    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_getApp()->getCache();
    }

    /**
     * Loading cache data
     *
     * @param   string $id
     * @return  mixed
     */
    public function loadCache($id)
    {
        return $this->_getApp()->loadCache($id);
    }

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $id
     * @param array $tags
     * @param bool $lifeTime
     * @return Mage_Core_Model_AppInterface
     */
    public function saveCache($data, $id, $tags = array(), $lifeTime = false)
    {
        return $this->_getApp()->saveCache($data, $id, $tags, $lifeTime);
    }

    /**
     * Remove cache
     *
     * @param   string $id
     * @return  Mage_Core_Model_AppInterface
     */
    public function removeCache($id)
    {
        return $this->_getApp()->removeCache($id);
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  Mage_Core_Model_AppInterface
     */
    public function cleanCache($tags = array())
    {
        return $this->_getApp()->cleanCache($tags);
    }

    /**
     * Check whether to use cache for specific component
     *
     * @param null|string $type
     * @return boolean
     */
    public function useCache($type = null)
    {
        return $this->_getApp()->useCache($type);
    }

    /**
     * Save cache usage settings
     *
     * @param array $data
     * @return Mage_Core_Model_AppInterface
     */
    public function saveUseCache($data)
    {
        return $this->_getApp()->saveUseCache($data);
    }

    /**
     * Deletes all session files
     *
     * @return Mage_Core_Model_AppInterface
     */
    public function cleanAllSessions()
    {
        return $this->_getApp()->cleanAllSessions();
    }

    /**
     * Retrieve request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_getApp()->getRequest();
    }

    /**
     * Request setter
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_Core_Model_AppInterface
     */
    public function setRequest(Mage_Core_Controller_Request_Http $request)
    {
        return $this->_getApp()->setRequest($request);
    }

    /**
     * Retrieve response object
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        return $this->_getApp()->getResponse();
    }

    /**
     * Response setter
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @return Mage_Core_Model_AppInterface
     */
    public function setResponse(Mage_Core_Controller_Response_Http $response)
    {
        return $this->_getApp()->setResponse($response);
    }

   /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function throwStoreException()
    {
        $this->_getApp()->throwStoreException();
    }

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return Mage_Core_Model_AppInterface
     */
    public function setUseSessionVar($var)
    {
        return $this->_getApp()->setUseSessionVar($var);
    }

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     */
    public function getUseSessionVar()
    {
        return $this->_getApp()->getUseSessionVar();
    }

    /**
     * Get either default or any store view
     *
     * @return Mage_Core_Model_Store
     */
    public function getAnyStoreView()
    {
        return $this->_getApp()->getAnyStoreView();
    }

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return Mage_Core_Model_AppInterface
     */
    public function setUseSessionInUrl($flag = true)
    {
        return $this->_getApp()->setUseSessionInUrl($flag);
    }

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     */
    public function getUseSessionInUrl()
    {
        return $this->_getApp()->getUseSessionInUrl();
    }

    /**
     * Allow or disallow single store mode
     *
     * @param bool $value
     */
    public function setIsSingleStoreModeAllowed($value)
    {
        $this->_getApp()->setIsSingleStoreModeAllowed($value);
    }

    /**
     * Prepare array of store groups
     * can be filtered to contain default store group or not by $withDefault flag
     * depending on flag $codeKey array keys can be group id or group code
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return array
     */
    public function getGroups($withDefault = false, $codeKey = false)
    {
        return $this->_getApp()->getGroups($withDefault, $codeKey);
    }

    /**
     *  Unset website by id from app cache
     *
     * @param null|bool|int|string|Mage_Core_Model_Website $id
     */
    public function clearWebsiteCache($id = null)
    {
        $this->_getApp()->clearWebsiteCache($id);
    }

    /**
     * Check if developer mode is enabled.
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        return $this->_getApp()->isDeveloperMode();
    }
}
