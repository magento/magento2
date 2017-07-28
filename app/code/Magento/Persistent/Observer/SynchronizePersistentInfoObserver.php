<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Persistent Session Observer
 * @since 2.0.0
 */
class SynchronizePersistentInfoObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.0.0
     */
    protected $_persistentSession;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData = null;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_persistentData = $persistentData;
        $this->_persistentSession = $persistentSession;
        $this->_customerSession = $customerSession;
    }

    /**
     * Synchronize persistent session info
     *
     * @param Observer $observer
     * @return void
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    public function execute(Observer $observer)
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
            $request && $request->getFullActionName() == 'customer_account_logout'
        ) {
            $sessionModel->save();
        }
    }
}
