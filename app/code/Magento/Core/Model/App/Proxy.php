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
 * obtain it through the world-wide-web, please send an e-mail
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
namespace Magento\Core\Model\App;

class Proxy implements \Magento\Core\Model\AppInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app = null;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get application model
     *
     * @return \Magento\Core\Model\App
     */
    protected function _getApp()
    {
        if (null === $this->_app) {
            $this->_app = $this->_objectManager->get('Magento\Core\Model\App');
        }

        return $this->_app;
    }

    /**
     * Run application. Run process responsible for request processing and sending response.
     *
     * @return \Magento\Core\Model\AppInterface
     */
    public function run()
    {
        return $this->_getApp()->run();
    }

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @throws \Magento\Exception
     */
    public function requireInstalledInstance()
    {
        $this->_getApp()->requireInstalledInstance();
    }

    /**
     * Retrieve cookie object
     *
     * @return \Magento\Core\Model\Cookie
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
     * @return  \Magento\Core\Model\AppInterface
     */
    public function setErrorHandler($handler)
    {
        return $this->_getApp()->setErrorHandler($handler);
    }

    /**
     * Loading application area
     *
     * @param   string $code
     * @return  \Magento\Core\Model\AppInterface
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
     * @return  \Magento\Core\Model\AppInterface
     */
    public function loadAreaPart($area, $part)
    {
        return $this->_getApp()->loadAreaPart($area, $part);
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Core\Model\App\Area
     */
    public function getArea($code)
    {
        return $this->_getApp()->getArea($code);
    }

    /**
     * Retrieve application store object
     *
     * @param null|string|bool|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
     * @throws \Magento\Core\Model\Store\Exception
     */
    public function getStore($storeId = null)
    {
        return $this->_getApp()->getStore($storeId);
    }

    /**
     * Retrieve application store object without Store_Exception
     *
     * @param string|int|\Magento\Core\Model\Store $storeId
     * @return \Magento\Core\Model\Store
     */
    public function getSafeStore($storeId = null)
    {
        return $this->_getApp()->getSafeStore($storeId);
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
     * @return \Magento\Core\Model\Store
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
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     * @return \Magento\Core\Model\Website
     * @throws \Magento\Core\Exception
     */
    public function getWebsite($websiteId = null)
    {
        return $this->_getApp()->getWebsite($websiteId);
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
     * @param null|\Magento\Core\Model\Store\Group|string $groupId
     * @return \Magento\Core\Model\Store\Group
     * @throws \Magento\Core\Exception
     */
    public function getGroup($groupId = null)
    {
        return $this->_getApp()->getGroup($groupId);
    }

    /**
     * Retrieve application locale object
     *
     * @return \Magento\Core\Model\LocaleInterface
     */
    public function getLocale()
    {
        return $this->_getApp()->getLocale();
    }

    /**
     * Retrieve layout object
     *
     * @return \Magento\Core\Model\Layout
     */
    public function getLayout()
    {
        return $this->_getApp()->getLayout();
    }

    /**
     * Retrieve helper object
     *
     * @param string $name
     * @return \Magento\Core\Helper\AbstractHelper
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
     * @return \Magento\Core\Model\Config
     */
    public function getConfig()
    {
        return $this->_getApp()->getConfig();
    }

    /**
     * Retrieve front controller object
     *
     * @return \Magento\Core\Controller\Varien\Front
     */
    public function getFrontController()
    {
        return $this->_getApp()->getFrontController();
    }

    /**
     * Get core cache model
     *
     * @return \Magento\Core\Model\CacheInterface
     */
    public function getCacheInstance()
    {
        return $this->_getApp()->getCacheInstance();
    }

    /**
     * Retrieve cache object
     *
     * @return \Zend_Cache_Core
     */
    public function getCache()
    {
        return $this->_getApp()->getCache();
    }

    /**
     * Loading cache data
     *
     * @param   string $cacheId
     * @return  mixed
     */
    public function loadCache($cacheId)
    {
        return $this->_getApp()->loadCache($cacheId);
    }

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $cacheId
     * @param array $tags
     * @param bool $lifeTime
     * @return \Magento\Core\Model\AppInterface
     */
    public function saveCache($data, $cacheId, $tags = array(), $lifeTime = false)
    {
        return $this->_getApp()->saveCache($data, $cacheId, $tags, $lifeTime);
    }

    /**
     * Remove cache
     *
     * @param   string $cacheId
     * @return  \Magento\Core\Model\AppInterface
     */
    public function removeCache($cacheId)
    {
        return $this->_getApp()->removeCache($cacheId);
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  \Magento\Core\Model\AppInterface
     */
    public function cleanCache($tags = array())
    {
        return $this->_getApp()->cleanCache($tags);
    }

    /**
     * Deletes all session files
     *
     * @return \Magento\Core\Model\AppInterface
     */
    public function cleanAllSessions()
    {
        return $this->_getApp()->cleanAllSessions();
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Core\Controller\Request\Http
     */
    public function getRequest()
    {
        return $this->_getApp()->getRequest();
    }

    /**
     * Request setter
     *
     * @param \Magento\Core\Controller\Request\Http $request
     * @return \Magento\Core\Model\AppInterface
     */
    public function setRequest(\Magento\Core\Controller\Request\Http $request)
    {
        return $this->_getApp()->setRequest($request);
    }

    /**
     * Retrieve response object
     *
     * @return \Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        return $this->_getApp()->getResponse();
    }

    /**
     * Response setter
     *
     * @param \Magento\Core\Controller\Response\Http $response
     * @return \Magento\Core\Model\AppInterface
     */
    public function setResponse(\Magento\Core\Controller\Response\Http $response)
    {
        return $this->_getApp()->setResponse($response);
    }

   /**
     * @throws \Magento\Core\Model\Store\Exception
     */
    public function throwStoreException()
    {
        $this->_getApp()->throwStoreException();
    }

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return \Magento\Core\Model\AppInterface
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
     * @return \Magento\Core\Model\Store
     */
    public function getAnyStoreView()
    {
        return $this->_getApp()->getAnyStoreView();
    }

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return \Magento\Core\Model\AppInterface
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
     * @param null|bool|int|string|\Magento\Core\Model\Website $websiteId
     */
    public function clearWebsiteCache($websiteId = null)
    {
        $this->_getApp()->clearWebsiteCache($websiteId);
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
