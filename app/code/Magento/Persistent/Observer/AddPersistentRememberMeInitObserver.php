<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Persistent\Helper\Data;

/**
 * Observer to add layout handle for persistent remember me init
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AddPersistentRememberMeInitObserver implements ObserverInterface
{

    /**
     * @param Data $persistentData
     * @param Session $customerSession
     */
    public function __construct(
        private Data    $persistentData,
        private Session $customerSession,
    ) {
    }

    /**
     * Apply persistent remember me init config to layout on certain conditions
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer): static
    {
        if ($this->customerSession->isLoggedIn()
            || !$this->persistentData->isEnabled()
            || !$this->persistentData->isRememberMeEnabled()
        ) {
            return $this;
        }

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getEvent()->getData('layout');
        $layout->getUpdate()->addHandle('remember_me');
        return $this;
    }
}
