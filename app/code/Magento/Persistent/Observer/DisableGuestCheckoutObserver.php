<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;

class DisableGuestCheckoutObserver implements ObserverInterface
{
    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * @param \Magento\Persistent\Helper\Session $persistentSession
     */
    public function __construct(\Magento\Persistent\Helper\Session $persistentSession)
    {
        $this->_persistentSession = $persistentSession;
    }

    /**
     * Disable guest checkout if we are in persistent mode
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_persistentSession->isPersistent()) {
            $observer->getEvent()->getResult()->setIsAllowed(false);
        }
    }
}
