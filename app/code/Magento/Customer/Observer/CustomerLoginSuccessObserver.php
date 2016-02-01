<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\ResourceModel\LockoutManagement;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

/**
 * Class CustomerLoginSuccessObserver
 */
class CustomerLoginSuccessObserver implements ObserverInterface
{
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
     * @param LockoutManagement $lockoutManager
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        LockoutManagement $lockoutManager,
        AccountManagementHelper $accountManagementHelper
    ) {
        $this->lockoutManager = $lockoutManager;
        $this->accountManagementHelper = $accountManagementHelper;
    }

    /**
     * Unlock customer on success login attempt.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Model\Customer $model */
        $customerModel = $observer->getEvent()->getData('model');
        $this->lockoutManager->unlock($customerModel->getId());
        $this->accountManagementHelper->reindexCustomer($customerModel->getId());
        return $this;
    }
}
