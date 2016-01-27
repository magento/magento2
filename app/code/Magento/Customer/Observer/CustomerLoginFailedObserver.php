<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\LockoutManagement;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

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
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param LockoutManagement $lockoutManager
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        LockoutManagement $lockoutManager,
        AccountManagementHelper $accountManagementHelper
    ) {
        $this->customerRegistry = $customerRegistry;
        $this->lockoutManager = $lockoutManager;
        $this->accountManagementHelper = $accountManagementHelper;
    }

    /**
     * Customer locking implementation.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $username = $observer->getEvent()->getData('username');
        $customer = $this->customerRegistry->retrieveByEmail($username);
        if ($customer && $customer->getId()) {
            $this->lockoutManager->processLockout($customer);
            $this->accountManagementHelper->reindexCustomer($customer->getId());
        }
        return $this;
    }
}
