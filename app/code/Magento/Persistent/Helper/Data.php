<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Persistent Shopping Cart Data Helper
 */
namespace Magento\Persistent\Helper;

use Magento\Framework\Module\Dir;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'persistent/options/enabled';

    const XML_PATH_LIFE_TIME = 'persistent/options/lifetime';

    const XML_PATH_LOGOUT_CLEAR = 'persistent/options/logout_clear';

    const XML_PATH_REMEMBER_ME_ENABLED = 'persistent/options/remember_enabled';

    const XML_PATH_REMEMBER_ME_DEFAULT = 'persistent/options/remember_default';

    const XML_PATH_PERSIST_SHOPPING_CART = 'persistent/options/shopping_cart';

    /**
     * Name of config file
     *
     * @var string
     */
    protected $_configFileName = 'persistent.xml';

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_modulesReader = $modulesReader;
        $this->_escaper = $escaper;
        parent::__construct(
            $context
        );
    }

    /**
     * Checks whether Persistence Functionality is enabled
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     * @codeCoverageIgnore
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Checks whether "Remember Me" enabled
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     * @codeCoverageIgnore
     */
    public function isRememberMeEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMEMBER_ME_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is "Remember Me" checked by default
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     * @codeCoverageIgnore
     */
    public function isRememberMeCheckedDefault($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMEMBER_ME_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is shopping cart persist
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return bool
     * @codeCoverageIgnore
     */
    public function isShoppingCartPersist($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PERSIST_SHOPPING_CART,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Persistence Lifetime
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return int
     */
    public function getLifeTime($store = null)
    {
        $lifeTime = intval(
            $this->scopeConfig->getValue(
                self::XML_PATH_LIFE_TIME,
                ScopeInterface::SCOPE_STORE,
                $store
            )
        );
        return $lifeTime < 0 ? 0 : $lifeTime;
    }

    /**
     * Check if set `Clear on Logout` in config settings
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @codeCoverageIgnore
     */
    public function getClearOnLogout()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_LOGOUT_CLEAR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve url for unset long-term cookie
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getUnsetCookieUrl()
    {
        return $this->_getUrl('persistent/index/unsetCookie');
    }

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return $this->_modulesReader->getModuleDir(Dir::MODULE_ETC_DIR, $this->_getModuleName())
        . '/' . $this->_configFileName;
    }

    /**
     * Check whether specified action should be processed
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function canProcess($observer)
    {
        return true;
    }
}
