<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 */
class SynchronizePersistentOnLoginObserver implements ObserverInterface
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
     * Constructor
     *
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->_customerSession = $customerSession;
        $this->_sessionFactory = $sessionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
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
}
