<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 */
class RenewCookieObserver implements ObserverInterface
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
     * Renew persistent cookie
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_persistentData->isEnabled()
            || !$this->_persistentSession->isPersistent()
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
