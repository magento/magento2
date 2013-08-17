<?php
/**
 * Application interface
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

interface Mage_Core_Model_AppInterface extends Mage_Core_Model_StoreManagerInterface
{
    /**
     * Default application locale
     */
    const DISTRO_LOCALE_CODE = 'en_US';

    /**
     * Default store Id (for install)
     */
    const DISTRO_STORE_ID       = 1;

    /**
     * Default store code (for install)
     *
     */
    const DISTRO_STORE_CODE     = Mage_Core_Model_Store::DEFAULT_CODE;

    /**
     * Admin store Id
     *
     */
    const ADMIN_STORE_ID = 0;

    /**
     * Dependency injection configuration node name
     */
    const CONFIGURATION_DI_NODE = 'di';

    /**
     * Run application. Run process responsible for request processing and sending response.
     *
     * @return Mage_Core_Model_AppInterface
     */
    public function run();

    /**
     * Throw an exception, if the application has not been installed yet
     *
     * @throws Magento_Exception
     */
    public function requireInstalledInstance();

    /**
     * Retrieve cookie object
     *
     * @return Mage_Core_Model_Cookie
     */
    public function getCookie();

   /**
     * Re-declare custom error handler
     *
     * @param   string $handler
     * @return  Mage_Core_Model_AppInterface
     */
    public function setErrorHandler($handler);

    /**
     * Loading application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_AppInterface
     */
    public function loadArea($code);

    /**
     * Loading part of area data
     *
     * @param   string $area
     * @param   string $part
     * @return  Mage_Core_Model_AppInterface
     */
    public function loadAreaPart($area, $part);

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  Mage_Core_Model_App_Area
     */
    public function getArea($code);

    /**
     * Get distributive locale code
     *
     * @return string
     */
    public function getDistroLocaleCode();

    /**
     * Retrieve application locale object
     *
     * @return Mage_Core_Model_LocaleInterface
     */
    public function getLocale();

    /**
     * Retrieve layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout();

    /**
     * Retrieve helper object
     *
     * @param string $name
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper($name);

    /**
     * Retrieve application base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Retrieve configuration object
     *
     * @return Mage_Core_Model_Config
     */
    public function getConfig();

    /**
     * Retrieve front controller object
     *
     * @return Mage_Core_Controller_Varien_Front
     */
    public function getFrontController();

    /**
     * Get core cache model
     *
     * @return Mage_Core_Model_CacheInterface
     */
    public function getCacheInstance();


    /**
     * Retrieve cache object
     *
     * @return Zend_Cache_Core
     */
    public function getCache();

    /**
     * Loading cache data
     *
     * @param   string $id
     * @return  mixed
     */
    public function loadCache($id);

    /**
     * Saving cache data
     *
     * @param mixed $data
     * @param string $id
     * @param array $tags
     * @param bool $lifeTime
     * @return Mage_Core_Model_AppInterface
     */
    public function saveCache($data, $id, $tags = array(), $lifeTime = false);

    /**
     * Remove cache
     *
     * @param   string $id
     * @return  Mage_Core_Model_AppInterface
     */
    public function removeCache($id);

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  Mage_Core_Model_AppInterface
     */
    public function cleanCache($tags = array());

    /**
     * Check whether to use cache for specific component
     *
     * @param null|string $type
     * @return boolean
     */
    public function useCache($type = null);

    /**
     * Deletes all session files
     *
     * @return Mage_Core_Model_AppInterface
     */
    public function cleanAllSessions();

    /**
     * Retrieve request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest();

    /**
     * Request setter
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @return Mage_Core_Model_AppInterface
     */
    public function setRequest(Mage_Core_Controller_Request_Http $request);

    /**
     * Retrieve response object
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse();

    /**
     * Response setter
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @return Mage_Core_Model_AppInterface
     */
    public function setResponse(Mage_Core_Controller_Response_Http $response);

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return Mage_Core_Model_AppInterface
     */
    public function setUseSessionVar($var);

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     */
    public function getUseSessionVar();

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return Mage_Core_Model_AppInterface
     */
    public function setUseSessionInUrl($flag = true);

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     */
    public function getUseSessionInUrl();

    /**
     * Check if developer mode is enabled.
     *
     * @return bool
     */
    public function isDeveloperMode();
}
