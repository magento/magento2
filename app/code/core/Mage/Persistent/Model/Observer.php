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
 * Persistent Observer
 *
 * @category   Mage
 * @package    Mage_Persistent
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Persistent_Model_Observer
{
    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * Apply persistent data
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Persistent_Model_Observer
     */
    public function applyPersistentData($observer)
    {
        $helper = Mage::helper('Mage_Persistent_Helper_Data');
        if (!$helper->canProcess($observer)
            || !$this->_getPersistentHelper()->isPersistent()
            || Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
        ) {
            return $this;
        }
        Mage::getModel('Mage_Persistent_Model_Persistent_Config')
            ->setConfigFilePath($helper->getPersistentConfigFilePath())
            ->fire();
        return $this;
    }

    /**
     * Apply persistent data to specific block
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Persistent_Model_Observer
     */
    public function applyBlockPersistentData($observer)
    {
        if (!$this->_getPersistentHelper()->isPersistent() || Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            return $this;
        }

        /** @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();

        if (!$block) {
            return $this;
        }

        $xPath = '//instances/blocks/*[block_type="' . get_class($block) . '"]';
        $configFilePath = $observer->getEvent()->getConfigFilePath();
        if (!$configFilePath) {
            $configFilePath = Mage::helper('Mage_Persistent_Helper_Data')->getPersistentConfigFilePath();
        }

        /** @var $persistentConfig Mage_Persistent_Model_Persistent_Config */
        $persistentConfig = Mage::getModel('Mage_Persistent_Model_Persistent_Config')
            ->setConfigFilePath($configFilePath);

        foreach ($persistentConfig->getXmlConfig()->xpath($xPath) as $persistentConfigInfo) {
            $persistentConfig->fireOne($persistentConfigInfo->asArray(), $block);
        }

        return $this;
    }

    /**
     * Emulate 'welcome' block with persistent data
     *
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Persistent_Model_Observer
     */
    public function emulateWelcomeBlock($block)
    {
        $escapedName = Mage::helper('Mage_Core_Helper_Data')->escapeHtml(
            $this->_getPersistentCustomer()->getName(),
            null
        );

        $block->setWelcome(Mage::helper('Mage_Persistent_Helper_Data')->__('Welcome, %s!', $escapedName));

        $this->_applyAccountLinksPersistentData();
        $block->setAdditionalHtml(Mage::app()->getLayout()->getBlock('header.additional')->toHtml());

        return $this;
    }

    /**
     * Emulate 'account links' block with persistent data
     */
    protected function _applyAccountLinksPersistentData()
    {
        if (!Mage::app()->getLayout()->getBlock('header.additional')) {
            Mage::app()->getLayout()->addBlock('Mage_Persistent_Block_Header_Additional', 'header.additional');
        }
    }

    /**
     * Emulate 'account links' block with persistent data
     *
     * @param Mage_Core_Block_Abstract $block
     */
    public function emulateAccountLinks($block)
    {
        $this->_applyAccountLinksPersistentData();
        $block->getCacheKeyInfo();
        $helper = Mage::helper('Mage_Persistent_Helper_Data');
        $block->addLink(
            $helper->getPersistentName(),
            $helper->getUnsetCookieUrl(),
            $helper->getPersistentName(),
            false,
            array(),
            110
        );
        $customerHelper = Mage::helper('Mage_Customer_Helper_Data');
        $block->removeLinkByUrl($customerHelper->getRegisterUrl());
        $block->removeLinkByUrl($customerHelper->getLoginUrl());
    }

    /**
     * Emulate 'top links' block with persistent data
     *
     * @param Mage_Core_Block_Abstract $block
     */
    public function emulateTopLinks($block)
    {
        $this->_applyAccountLinksPersistentData();
        $block->removeLinkByUrl(Mage::getUrl('customer/account/login'));
    }

    /**
     * Emulate quote by persistent data
     *
     * @param Varien_Event_Observer $observer
     */
    public function emulateQuote($observer)
    {
        $stopActions = array(
            'persistent_index_saveMethod',
            'customer_account_createpost'
        );

        if (!Mage::helper('Mage_Persistent_Helper_Data')->canProcess($observer)
            || !$this->_getPersistentHelper()->isPersistent()
            || Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
        ) {
            return;
        }

        /** @var $action Mage_Checkout_OnepageController */
        $action = $observer->getEvent()->getControllerAction();
        $actionName = $action->getFullActionName();

        if (in_array($actionName, $stopActions)) {
            return;
        }

        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('Mage_Checkout_Model_Session');
        if ($this->_isShoppingCartPersist()) {
            $checkoutSession->setCustomer($this->_getPersistentCustomer());
            if (!$checkoutSession->hasQuote()) {
                $checkoutSession->getQuote();
            }
        }
    }

    /**
     * Set persistent data into quote
     *
     * @param Varien_Event_Observer $observer
     */
    public function setQuotePersistentData($observer)
    {
        if (!$this->_isPersistent()) {
            return;
        }

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        if ($this->_isGuestShoppingCart() && $this->_setQuotePersistent) {
            //Quote is not actual customer's quote, just persistent
            $quote->setIsActive(false)->setIsPersistent(true);
        }
    }

    /**
     * Set quote to be loaded even if not active
     *
     * @param Varien_Event_Observer $observer
     */
    public function setLoadPersistentQuote($observer)
    {
        if (!$this->_isGuestShoppingCart()) {
            return;
        }

        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = $observer->getEvent()->getCheckoutSession();
        if ($checkoutSession) {
            $checkoutSession->setLoadInactive();
        }
    }

    /**
     * Prevent clear checkout session
     *
     * @param Varien_Event_Observer $observer
     */
    public function preventClearCheckoutSession($observer)
    {
        $action = $this->_checkClearCheckoutSessionNecessity($observer);

        if ($action) {
            $action->setClearCheckoutSession(false);
        }
    }

    /**
     * Make persistent quote to be guest
     *
     * @param Varien_Event_Observer $observer
     */
    public function makePersistentQuoteGuest($observer)
    {
        if (!$this->_checkClearCheckoutSessionNecessity($observer)) {
            return;
        }

        $this->setQuoteGuest(true);
    }

    /**
     * Check if checkout session should NOT be cleared
     *
     * @param Varien_Event_Observer $observer
     * @return bool|Mage_Persistent_IndexController
     */
    protected function _checkClearCheckoutSessionNecessity($observer)
    {
        if (!$this->_isGuestShoppingCart()) {
            return false;
        }

        /** @var $action Mage_Persistent_IndexController */
        $action = $observer->getEvent()->getControllerAction();
        if ($action instanceof Mage_Persistent_IndexController) {
            return $action;
        }

        return false;
    }

    /**
     * Reset session data when customer re-authenticates
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerAuthenticatedEvent($observer)
    {
        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('Mage_Customer_Model_Session');
        $customerSession->setCustomerId(null)
            ->setCustomerGroupId(null);

        if (Mage::app()->getRequest()->getParam('context') != 'checkout') {
            $this->_expirePersistentSession();
            return;
        }

        $this->setQuoteGuest();
    }

    /**
     * Unset persistent cookie and make customer's quote as a guest
     *
     * @param Varien_Event_Observer $observer
     */
    public function removePersistentCookie($observer)
    {
        if (!Mage::helper('Mage_Persistent_Helper_Data')->canProcess($observer) || !$this->_isPersistent()) {
            return;
        }

        $this->_getPersistentHelper()->getSession()->removePersistentCookie();
        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('Mage_Customer_Model_Session');
        if (!$customerSession->isLoggedIn()) {
            $customerSession->setCustomerId(null)
                ->setCustomerGroupId(null);
        }

        $this->setQuoteGuest();
    }

    /**
     * Disable guest checkout if we are in persistent mode
     *
     * @param Varien_Event_Observer $observer
     */
    public function disableGuestCheckout($observer)
    {
        if ($this->_getPersistentHelper()->isPersistent()) {
            $observer->getEvent()->getResult()->setIsAllowed(false);
        }
    }

    /**
     * Prevent express checkout with Google checkout and PayPal Express checkout
     *
     * @param Varien_Event_Observer $observer
     */
    public function preventExpressCheckout($observer)
    {
        if (!$this->_isLoggedOut()) {
            return;
        }

        /** @var $controllerAction Mage_Core_Controller_Front_Action */
        $controllerAction = $observer->getEvent()->getControllerAction();
        if (is_callable(array($controllerAction, 'redirectLogin'))) {
            Mage::getSingleton('Mage_Core_Model_Session')->addNotice(
                Mage::helper('Mage_Persistent_Helper_Data')->__('To proceed to Checkout, please log in using your email address.')
            );
            $controllerAction->redirectLogin();
            if ($controllerAction instanceof Mage_GoogleCheckout_RedirectController
                || $controllerAction instanceof Mage_Paypal_Controller_Express_Abstract
            ) {
                Mage::getSingleton('Mage_Customer_Model_Session')
                    ->setBeforeAuthUrl(Mage::getUrl('persistent/index/expressCheckout'));
            }
        }
    }

    /**
     * Retrieve persistent customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getPersistentCustomer()
    {
        return Mage::getModel('Mage_Customer_Model_Customer')->load(
            $this->_getPersistentHelper()->getSession()->getCustomerId()
        );
    }

    /**
     * Retrieve persistent helper
     *
     * @return Mage_Persistent_Helper_Session
     */
    protected function _getPersistentHelper()
    {
        return Mage::helper('Mage_Persistent_Helper_Session');
    }

    /**
     * Return current active quote for persistent customer
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        $quote = Mage::getModel('Mage_Sales_Model_Quote');
        $quote->loadByCustomer($this->_getPersistentCustomer());
        return $quote;
    }

    /**
     * Check whether shopping cart is persistent
     *
     * @return bool
     */
    protected function _isShoppingCartPersist()
    {
        return Mage::helper('Mage_Persistent_Helper_Data')->isShoppingCartPersist();
    }

    /**
     * Check whether persistent mode is running
     *
     * @return bool
     */
    protected function _isPersistent()
    {
        return $this->_getPersistentHelper()->isPersistent();
    }

    /**
     * Check if persistent mode is running and customer is logged out
     *
     * @return bool
     */
    protected function _isLoggedOut()
    {
        return $this->_isPersistent() && !Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn();
    }

    /**
     * Check if shopping cart is guest while persistent session and user is logged out
     *
     * @return bool
     */
    protected function _isGuestShoppingCart()
    {
        return $this->_isLoggedOut() && !Mage::helper('Mage_Persistent_Helper_Data')->isShoppingCartPersist();
    }

    /**
     * Make quote to be guest
     *
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     */
    public function setQuoteGuest($checkQuote = false)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
        if ($quote && $quote->getId()) {
            if ($checkQuote
                && !Mage::helper('Mage_Persistent_Helper_Data')->isShoppingCartPersist()
                && !$quote->getIsPersistent()
            ) {
                Mage::getSingleton('Mage_Checkout_Model_Session')->unsetAll();
                return;
            }

            $quote->getPaymentsCollection()->walk('delete');
            $quote->getAddressesCollection()->walk('delete');
            $this->_setQuotePersistent = false;
            $quote
                ->setIsActive(true)
                ->setCustomerId(null)
                ->setCustomerEmail(null)
                ->setCustomerFirstname(null)
                ->setCustomerLastname(null)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
                ->setIsPersistent(false)
                ->removeAllAddresses();
            //Create guest addresses
            $quote->getShippingAddress();
            $quote->getBillingAddress();
            $quote->collectTotals()->save();
        }

        $this->_getPersistentHelper()->getSession()->removePersistentCookie();
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param Varien_Event_Observer $observer
     */
    public function checkExpirePersistentQuote(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('Mage_Persistent_Helper_Data')->canProcess($observer)) {
            return;
        }

        /** @var $customerSession Mage_Customer_Model_Session */
        $customerSession = Mage::getSingleton('Mage_Customer_Model_Session');

        if (Mage::helper('Mage_Persistent_Helper_Data')->isEnabled()
            && !$this->_isPersistent()
            && !$customerSession->isLoggedIn()
            && Mage::getSingleton('Mage_Checkout_Model_Session')->getQuoteId()
        ) {
            Mage::dispatchEvent('persistent_session_expired');
            $this->_expirePersistentSession();
            $customerSession->setCustomerId(null)->setCustomerGroupId(null);
        }
    }

    protected function _expirePersistentSession()
    {
        /** @var $checkoutSession Mage_Checkout_Model_Session */
        $checkoutSession = Mage::getSingleton('Mage_Checkout_Model_Session');

        $quote = $checkoutSession->setLoadInactive()->getQuote();
        if ($quote->getIsActive() && $quote->getCustomerId()) {
            $checkoutSession->setCustomer(null)->unsetAll();
        } else {
            $quote
                ->setIsActive(true)
                ->setIsPersistent(false)
                ->setCustomerId(null)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }
    }

    /**
     * Clear expired persistent sessions
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Persistent_Model_Observer_Cron
     */
    public function clearExpiredCronJob(Mage_Cron_Model_Schedule $schedule)
    {
        $websiteIds = Mage::getResourceModel('Mage_Core_Model_Resource_Website_Collection')->getAllIds();
        if (!is_array($websiteIds)) {
            return $this;
        }

        foreach ($websiteIds as $websiteId) {
            Mage::getModel('Mage_Persistent_Model_Session')->deleteExpired($websiteId);
        }

        return $this;
    }

    /**
     * Create handle for persistent session if persistent cookie and customer not logged in
     *
     * @param Varien_Event_Observer $observer
     */
    public function createPersistentHandleLayout(Varien_Event_Observer $observer)
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $observer->getEvent()->getLayout();
        $helper = Mage::helper('Mage_Persistent_Helper_Data');
        if ($helper->canProcess($observer)
            && $layout && $helper->isEnabled()
            && Mage::helper('Mage_Persistent_Helper_Session')->isPersistent()
        ) {
            $handle = (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn())
                ? Mage_Persistent_Helper_Data::LOGGED_IN_LAYOUT_HANDLE
                : Mage_Persistent_Helper_Data::LOGGED_OUT_LAYOUT_HANDLE;
            $layout->getUpdate()->addHandle($handle);
        }
    }

    /**
     * Update customer id and customer group id if user is in persistent session
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateCustomerCookies(Varien_Event_Observer $observer)
    {
        if (!$this->_isPersistent()) {
            return;
        }

        $customerCookies = $observer->getEvent()->getCustomerCookies();
        if ($customerCookies instanceof Varien_Object) {
            $persistentCustomer = $this->_getPersistentCustomer();
            $customerCookies->setCustomerId($persistentCustomer->getId());
            $customerCookies->setCustomerGroupId($persistentCustomer->getGroupId());
        }
    }

    /**
     * Set persistent data to customer session
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Persistent_Model_Observer
     */
    public function emulateCustomer($observer)
    {
        if (!Mage::helper('Mage_Persistent_Helper_Data')->canProcess($observer)
            || !$this->_isShoppingCartPersist()
        ) {
            return $this;
        }

        if ($this->_isLoggedOut()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('Mage_Customer_Model_Customer')->load(
                $this->_getPersistentHelper()->getSession()->getCustomerId()
            );
            Mage::getSingleton('Mage_Customer_Model_Session')
                ->setCustomerId($customer->getId())
                ->setCustomerGroupId($customer->getGroupId());
        }
        return $this;
    }
}
