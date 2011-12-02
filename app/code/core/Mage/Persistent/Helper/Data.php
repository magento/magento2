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
 * @package     Mage_Persistent
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Persistent Shopping Cart Data Helper
 *
 * @category   Mage
 * @package    Mage_Persistent
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Persistent_Helper_Data extends Mage_Core_Helper_Data
{
    const XML_PATH_ENABLED = 'persistent/options/enabled';
    const XML_PATH_LIFE_TIME = 'persistent/options/lifetime';
    const XML_PATH_LOGOUT_CLEAR = 'persistent/options/logout_clear';
    const XML_PATH_REMEMBER_ME_ENABLED = 'persistent/options/remember_enabled';
    const XML_PATH_REMEMBER_ME_DEFAULT = 'persistent/options/remember_default';
    const XML_PATH_PERSIST_SHOPPING_CART = 'persistent/options/shopping_cart';

    const LOGGED_IN_LAYOUT_HANDLE = 'customer_logged_in_psc_handle';
    const LOGGED_OUT_LAYOUT_HANDLE = 'customer_logged_out_psc_handle';

    /**
     * Name of config file
     *
     * @var string
     */
    protected $_configFileName = 'persistent.xml';

    /**
     * Checks whether Persistence Functionality is enabled
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED, $store);
    }

    /**
     * Checks whether "Remember Me" enabled
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isRememberMeEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_REMEMBER_ME_ENABLED, $store);
    }

    /**
     * Is "Remember Me" checked by default
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isRememberMeCheckedDefault($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_REMEMBER_ME_DEFAULT, $store);
    }

    /**
     * Is shopping cart persist
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return bool
     */
    public function isShoppingCartPersist($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_PERSIST_SHOPPING_CART, $store);
    }

    /**
     * Get Persistence Lifetime
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return int
     */
    public function getLifeTime($store = null)
    {
        $lifeTime = intval(Mage::getStoreConfig(self::XML_PATH_LIFE_TIME, $store));
        return ($lifeTime < 0) ? 0 : $lifeTime;
    }

    /**
     * Check if set `Clear on Logout` in config settings
     *
     * @return bool
     */
    public function getClearOnLogout()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_LOGOUT_CLEAR);
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
        return $this->__('(Not %s?)', $this->escapeHtml(Mage::helper('Mage_Persistent_Helper_Session')->getCustomer()->getName()));
    }

    /**
     * Retrieve path for config file
     *
     * @return string
     */
    public function getPersistentConfigFilePath()
    {
        return Mage::getConfig()->getModuleDir('etc', $this->_getModuleName()) . DS . $this->_configFileName;
    }

    /**
     * Check whether specified action should be processed
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function canProcess($observer)
    {
        $action = $observer->getEvent()->getAction();
        $controllerAction = $observer->getEvent()->getControllerAction();

        if ($action instanceof Mage_Core_Controller_Varien_Action) {
            return !$action->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION);
        }
        if ($controllerAction instanceof Mage_Core_Controller_Varien_Action) {
            return !$controllerAction->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION);
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
        if (Mage::helper('Mage_Checkout_Helper_Data')->isContextCheckout()) {
            $url = Mage::helper('Mage_Core_Helper_Url')->addRequestParam($url, array('context' => 'checkout'));
        }
        return $url;
    }

}
