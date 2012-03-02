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
 * @package     Mage_PageCache
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page cache data helper
 *
 * @category    Mage
 * @package     Mage_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_PageCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Pathes to external cache config options
     */
    const XML_PATH_EXTERNAL_CACHE_ENABLED  = 'system/external_page_cache/enabled';
    const XML_PATH_EXTERNAL_CACHE_LIFETIME = 'system/external_page_cache/cookie_lifetime';
    const XML_PATH_EXTERNAL_CACHE_CONTROL  = 'system/external_page_cache/control';

    /**
     * Path to external cache controls
     */
    const XML_PATH_EXTERNAL_CACHE_CONTROLS = 'global/external_cache/controls';

    /**
     * Cookie name for disabling external caching
     *
     * @var string
     */
    const NO_CACHE_COOKIE = 'external_no_cache';

    /**
     * Check whether external cache is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_ENABLED);
    }

    /**
     * Return all available external cache controls
     *
     * @return array
     */
    public function getCacheControls()
    {
        $controls = Mage::app()->getConfig()->getNode(self::XML_PATH_EXTERNAL_CACHE_CONTROLS);
        return $controls->asCanonicalArray();
    }

    /**
     * Initialize proper external cache control model
     *
     * @throws Mage_Core_Exception
     * @return Mage_PageCache_Model_Control_Interface
     */
    public function getCacheControlInstance()
    {
        $usedControl = Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_CONTROL);
        if ($usedControl) {
            foreach ($this->getCacheControls() as $control => $info) {
                if ($control == $usedControl && !empty($info['class'])) {
                    return Mage::getSingleton($info['class']);
                }
            }
        }
        Mage::throwException($this->__('Failed to load external cache control'));
    }

    /**
     * Disable caching on external storage side by setting special cookie
     *
     * @return void
     */
    public function setNoCacheCookie()
    {
        $cookie   = Mage::getSingleton('Mage_Core_Model_Cookie');
        $lifetime = Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
        $noCache  = $cookie->get(self::NO_CACHE_COOKIE);

        if ($noCache) {
            $cookie->renew(self::NO_CACHE_COOKIE, $lifetime);
        } else {
            $cookie->set(self::NO_CACHE_COOKIE, 1, $lifetime);
        }
    }

    /**
     * Returns a lifetime of cookie for external cache
     *
     * @return string Time in seconds
     */
    public function getNoCacheCookieLifetime()
    {
        return Mage::getStoreConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
    }
}
