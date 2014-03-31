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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Persistent\Controller;

/**
 * Persistent front controller
 */
class Index extends \Magento\App\Action\Action
{
    /**
     * Whether clear checkout session when logout
     *
     * @var bool
     */
    protected $_clearCheckoutSession = true;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Persistent observer
     *
     * @var \Magento\Persistent\Model\Observer
     */
    protected $_persistentObserver;

    /**
     * Construct
     *
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Persistent\Model\Observer $persistentObserver
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Persistent\Model\Observer $persistentObserver,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_persistentObserver = $persistentObserver;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Set whether clear checkout session when logout
     *
     * @param bool $clear
     * @return $this
     */
    public function setClearCheckoutSession($clear = true)
    {
        $this->_clearCheckoutSession = $clear;
        return $this;
    }

    /**
     * Retrieve 'persistent session' helper instance
     *
     * @return \Magento\Persistent\Helper\Session
     */
    protected function _getHelper()
    {
        return $this->_objectManager->get('Magento\Persistent\Helper\Session');
    }

    /**
     * Unset persistent cookie action
     *
     * @return void
     */
    public function unsetCookieAction()
    {
        if ($this->_getHelper()->isPersistent()) {
            $this->_cleanup();
        }
        $this->_redirect('customer/account/login');
        return;
    }

    /**
     * Revert all persistent data
     *
     * @return $this
     */
    protected function _cleanup()
    {
        $this->_eventManager->dispatch('persistent_session_expired');
        $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        if ($this->_clearCheckoutSession) {
            $this->_checkoutSession->clearStorage();
        }
        $this->_getHelper()->getSession()->removePersistentCookie();
        return $this;
    }

    /**
     * Save onepage checkout method to be register
     *
     * @return void
     */
    public function saveMethodAction()
    {
        if ($this->_getHelper()->isPersistent()) {
            $this->_getHelper()->getSession()->removePersistentCookie();
            if (!$this->_customerSession->isLoggedIn()) {
                $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
            }

            $this->_persistentObserver->setQuoteGuest();
        }

        $checkoutUrl = $this->_redirect->getRefererUrl();
        $this->getResponse()->setRedirect($checkoutUrl . (strpos($checkoutUrl, '?') ? '&' : '?') . 'register');
    }

    /**
     * Add appropriate session message and redirect to shopping cart
     *
     * @return void
     */
    public function expressCheckoutAction()
    {
        $this->messageManager->addNotice(__('Your shopping cart has been updated with new prices.'));
        $this->_redirect('checkout/cart');
    }
}
