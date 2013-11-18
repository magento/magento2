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
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page cache data helper
 *
 * @category    Magento
 * @package     Magento_PageCache
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\PageCache\Helper;

class Data extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Paths to external cache config options
     */
    const XML_PATH_EXTERNAL_CACHE_ENABLED  = 'system/external_page_cache/enabled';
    const XML_PATH_EXTERNAL_CACHE_LIFETIME = 'system/external_page_cache/cookie_lifetime';

    /**
     * Cookie name for disabling external caching
     */
    const NO_CACHE_COOKIE = 'external_no_cache';

    /**
     * Cookie name for locking the NO_CACHE_COOKIE for modification
     */
    const NO_CACHE_LOCK_COOKIE = 'external_no_cache_cookie_locked';

    /**
     * @var bool
     */
    protected $_isNoCacheCookieLocked = false;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @param \Magento\Core\Helper\Context                 $context
     * @param \Magento\PageCache\Model\CacheControlFactory $ccFactory
     * @param \Magento\Core\Model\Cookie                   $cookie
     * @param \Magento\Core\Model\Store\Config             $coreStoreConfig
     */
    function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\PageCache\Model\CacheControlFactory $ccFactory,
        \Magento\Core\Model\Cookie $cookie,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        parent::__construct($context);
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_isNoCacheCookieLocked = (bool)$cookie->get(self::NO_CACHE_LOCK_COOKIE);
        $this->_cookie = $cookie;
        $this->_ccFactory = $ccFactory;
    }

    /**
     * Check whether external cache is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_ENABLED);
    }

    /**
     * Initialize proper external cache control model
     *
     * @throws \Magento\Core\Exception
     * @return \Magento\PageCache\Model\Control\ControlInterface
     */
    public function getCacheControlInstance()
    {
        return $this->_ccFactory->getCacheControlInstance();
    }

    /**
     * Disable caching on external storage side by setting special cookie, if the cookie has not been locked
     *
     * @param int|null $lifetime
     * @return \Magento\PageCache\Helper\Data
     */
    public function setNoCacheCookie($lifetime = null)
    {
        if ($this->_isNoCacheCookieLocked) {
            return $this;
        }
        $lifetime = $lifetime !== null ? $lifetime : $this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
        if ($this->_cookie->get(self::NO_CACHE_COOKIE)) {
            $this->_cookie->renew(self::NO_CACHE_COOKIE, $lifetime);
        } else {
            $this->_cookie->set(self::NO_CACHE_COOKIE, '1', $lifetime);
        }
        return $this;
    }

    /**
     * Remove the 'no cache' cookie, if it has not been locked
     *
     * @return \Magento\PageCache\Helper\Data
     */
    public function removeNoCacheCookie()
    {
        if (!$this->_isNoCacheCookieLocked) {
            $this->_cookie->delete(self::NO_CACHE_COOKIE);
        }
        return $this;
    }

    /**
     * Disable modification of the 'no cache' cookie
     *
     * @return \Magento\PageCache\Helper\Data
     */
    public function lockNoCacheCookie()
    {
        $this->_cookie->set(self::NO_CACHE_LOCK_COOKIE, '1', 0);
        $this->_isNoCacheCookieLocked = true;
        return $this;
    }

    /**
     * Enable modification of the 'no cache' cookie
     *
     * @return \Magento\PageCache\Helper\Data
     */
    public function unlockNoCacheCookie()
    {
        $this->_cookie->delete(self::NO_CACHE_LOCK_COOKIE);
        $this->_isNoCacheCookieLocked = false;
        return $this;
    }

    /**
     * Returns a lifetime of cookie for external cache
     *
     * @return string Time in seconds
     */
    public function getNoCacheCookieLifetime()
    {
        return $this->_coreStoreConfig->getConfig(self::XML_PATH_EXTERNAL_CACHE_LIFETIME);
    }
}
