<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Observer;

use Magento\Framework\Event\Observer;

/**
 * Persistent Session Observer
 */
class Session
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     */
    protected $_sessionFactory;

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
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_sessionFactory = $sessionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function synchronizePersistentOnLogin(Observer $observer)
    {
        /** @var $customer \Magento\Customer\Api\Data\CustomerInterface */
        $customer = $observer->getEvent()->getCustomer();
        // Check if customer is valid (remove persistent cookie for invalid customer)
        if (!$customer || !$customer->getId() || !$this->_persistentSession->isRememberMeChecked()) {
            $this->_sessionFactory->create()->removePersistentCookie();
            return;
        }

        $persistentLifeTime = $this->_persistentData->getLifeTime();
        // Delete persistent session, if persistent could not be applied
        if ($this->_persistentData->isEnabled() && $persistentLifeTime <= 0) {
            // Remove current customer persistent session
            $this->_sessionFactory->create()->deleteByCustomerId($customer->getId());
            return;
        }

        /** @var $sessionModel \Magento\Persistent\Model\Session */
        $sessionModel = $this->_persistentSession->getSession();

        // Check if session is wrong or not exists, so create new session
        if (!$sessionModel->getId() || $sessionModel->getCustomerId() != $customer->getId()) {
            /** @var \Magento\Persistent\Model\Session $sessionModel */
            $sessionModel = $this->_sessionFactory->create();
            $sessionModel->setLoadExpired()->loadByCustomerId($customer->getId());
            if (!$sessionModel->getId()) {
                /** @var \Magento\Persistent\Model\Session $sessionModel */
                $sessionModel = $this->_sessionFactory->create();
                $sessionModel->setCustomerId($customer->getId())->save();
            }
            $this->_persistentSession->setSession($sessionModel);
        }

        // Set new cookie
        if ($sessionModel->getId()) {
            $sessionModel->setPersistentCookie(
                $persistentLifeTime,
                $this->_customerSession->getCookiePath()
            );
        }
    }

    /**
     * Unload persistent session (if set in config)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Observer $observer
     * @return void
     */
    public function synchronizePersistentOnLogout(Observer $observer)
    {
        if (!$this->_persistentData->isEnabled() || !$this->_persistentData->getClearOnLogout()) {
            return;
        }

        $this->_sessionFactory->create()->removePersistentCookie();

        // Unset persistent session
        $this->_persistentSession->setSession(null);
    }

    /**
     * Synchronize persistent session info
     *
     * @param Observer $observer
     * @return void
     */
    public function synchronizePersistentInfo(Observer $observer)
    {
        if (!$this->_persistentData->isEnabled() || !$this->_persistentSession->isPersistent()) {
            return;
        }

        /** @var $sessionModel \Magento\Persistent\Model\Session */
        $sessionModel = $this->_persistentSession->getSession();

        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();

        // Quote Id could be changed only by logged in customer
        if ($this->_customerSession->isLoggedIn() ||
            $request && $request->getActionName() == 'logout' && $request->getControllerName() == 'account'
        ) {
            $sessionModel->save();
        }
    }

    /**
     * Set Checked status of "Remember Me"
     *
     * @param Observer $observer
     * @return void
     */
    public function setRememberMeCheckedStatus(Observer $observer)
    {
        if (!$this->_persistentData->canProcess(
            $observer
        ) || !$this->_persistentData->isEnabled() || !$this->_persistentData->isRememberMeEnabled()
        ) {
            return;
        }

        /** @var $controllerAction \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();
        if ($request) {
            $rememberMeCheckbox = $request->getPost('persistent_remember_me');
            $this->_persistentSession->setRememberMeChecked((bool)$rememberMeCheckbox);
            if ($request->getFullActionName() == 'checkout_onepage_saveBilling' ||
                $request->getFullActionName() == 'customer_account_createpost'
            ) {
                $this->_checkoutSession->setRememberMeChecked((bool)$rememberMeCheckbox);
            }
        }
    }

    /**
     * Renew persistent cookie
     *
     * @param Observer $observer
     * @return void
     */
    public function renewCookie(Observer $observer)
    {
        if (!$this->_persistentData->canProcess(
            $observer
        ) || !$this->_persistentData->isEnabled() || !$this->_persistentSession->isPersistent()
        ) {
            return;
        }

        /** @var $request \Magento\Framework\App\RequestInterface */
        $request = $observer->getEvent()->getRequest();

        if ($this->_customerSession->isLoggedIn() || $request->getFullActionName() == 'customer_account_logout') {
            $this->_sessionFactory->create()
                ->renewPersistentCookie(
                    $this->_persistentData->getLifeTime(),
                    $this->_customerSession->getCookiePath()
                );
        }
    }
}
