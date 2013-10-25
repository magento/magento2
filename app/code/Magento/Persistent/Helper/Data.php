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
 * @package     Magento_Persistent
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Persistent Shopping Cart Data Helper
 */
namespace Magento\Persistent\Helper;

class Data extends \Magento\Core\Helper\Data
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
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $_checkoutData;

    /**
     * Core url
     *
     * @var \Magento\Core\Helper\Url
     */
    protected $_coreUrl;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Http $coreHttp
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\Core\Model\Date $dateModel
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Encryption $encryptor
     * @param \Magento\Core\Helper\Url $coreUrl
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\Core\Model\Date $dateModel,
        \Magento\App\State $appState,
        \Magento\Core\Model\Encryption $encryptor,
        \Magento\Core\Helper\Url $coreUrl,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Persistent\Helper\Session $persistentSession,
        $dbCompatibleMode = true
    )
    {
        $this->_coreUrl = $coreUrl;
        $this->_checkoutData = $checkoutData;
        $this->_persistentSession = $persistentSession;
        parent::__construct($context, $eventManager, $coreHttp, $config, $coreStoreConfig, $storeManager,
            $locale, $dateModel, $appState, $encryptor, $dbCompatibleMode
        );
    }

    /**
     * Checks whether Persistence Functionality is enabled
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Checks whether "Remember Me" enabled
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isRememberMeEnabled($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_REMEMBER_ME_ENABLED, $store);
    }

    /**
     * Is "Remember Me" checked by default
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isRememberMeCheckedDefault($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_REMEMBER_ME_DEFAULT, $store);
    }

    /**
     * Is shopping cart persist
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @return bool
     */
    public function isShoppingCartPersist($store = null)
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_PERSIST_SHOPPING_CART, $store);
    }

    /**
     * Get Persistence Lifetime
     *
     * @param int|string|\Magento\Core\Model\Store $store
     * @return int
     */
    public function getLifeTime($store = null)
    {
        $lifeTime = intval($this->_coreStoreConfig->getConfig(self::XML_PATH_LIFE_TIME, $store));
        return ($lifeTime < 0) ? 0 : $lifeTime;
    }

    /**
     * Check if set `Clear on Logout` in config settings
     *
     * @return bool
     */
    public function getClearOnLogout()
    {
        return $this->_coreStoreConfig->getConfigFlag(self::XML_PATH_LOGOUT_CLEAR);
    }

    /**
     * Retrieve url for unset long-term cookie
     *
     * @return string
     */
    public function getUnsetCookieUrl()
    {
        return $this->_getUrl('persistent/index/unsetCookie');
    }

    /**
     * Retrieve name of persistent customer
     *
     * @return string
     */
    public function getPersistentName()
    {
        return __('(Not %1?)', $this->escapeHtml($this->_persistentSession->getCustomer()->getName()));
    }

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return $this->_config->getModuleDir('etc', $this->_getModuleName()) . DS . $this->_configFileName;
    }

    /**
     * Check whether specified action should be processed
     *
     * @param \Magento\Event\Observer $observer
     * @return bool
     */
    public function canProcess($observer)
    {
        $action = $observer->getEvent()->getAction();
        $controllerAction = $observer->getEvent()->getControllerAction();

        if ($action instanceof \Magento\Core\Controller\Varien\Action) {
            return !$action->getFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_START_SESSION);
        }
        if ($controllerAction instanceof \Magento\Core\Controller\Varien\Action) {
            return !$controllerAction->getFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_START_SESSION);
        }
        return true;
    }

    /**
     * Get create account url depends on checkout
     *
     * @param  $url string
     * @return string
     */
    public function getCreateAccountUrl($url)
    {
        if ($this->_checkoutData->isContextCheckout()) {
            $url = $this->_coreUrl->addRequestParam($url, array('context' => 'checkout'));
        }
        return $url;
    }

}
