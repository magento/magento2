<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\LockoutManagement;

/**
 * Class CustomerLoginFailedObserver
 */
class CustomerLoginFailedObserver implements ObserverInterface
{
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Lockout manager
     *
     * @var \Magento\Customer\Model\ResourceModel\LockoutManagement
     */
    protected $lockoutManager;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param LockoutManagement $lockoutManager
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        LockoutManagement $lockoutManager
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->lockoutManager = $lockoutManager;
    }

    /**
     * Customer locking implementation.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $credentials = $observer->getEvent()->getData('credentials');
        $customer = $this->customerRegistry->retrieveByEmail($credentials['username']);
        if ($customer && $customer->getId()) {
            $this->lockoutManager->processLockout($customer);
        }
        return $this;
    }
}
