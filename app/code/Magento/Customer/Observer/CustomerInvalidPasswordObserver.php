<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CustomerInvalidPasswordObserver
 */
class CustomerInvalidPasswordObserver implements ObserverInterface
{
    /**
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param AccountManagementHelper $accountManagementHelper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        AccountManagementHelper $accountManagementHelper,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->accountManagementHelper = $accountManagementHelper;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Customer locking implementation
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $username = $observer->getEvent()->getData('username');
        $customer = $this->customerRepository->get($username);
        if ($customer && $customer->getId()) {
            $this->accountManagementHelper->processCustomerLockoutData($customer->getId());
            $this->customerRepository->save($customer);
        }
        return $this;
    }
}
