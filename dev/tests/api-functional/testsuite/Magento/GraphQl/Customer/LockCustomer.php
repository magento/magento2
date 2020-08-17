<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Model\CustomerAuthUpdate;
use Magento\Customer\Model\CustomerRegistry;

/**
 * Lock customer account by ID
 */
class LockCustomer
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerAuthUpdate
     */
    private $customerAuthUpdate;

    /**
     * @param CustomerRegistry $customerRegistry
     * @param CustomerAuthUpdate $customerAuthUpdate
     */
    public function __construct(CustomerRegistry $customerRegistry, CustomerAuthUpdate $customerAuthUpdate)
    {
        $this->customerRegistry = $customerRegistry;
        $this->customerAuthUpdate = $customerAuthUpdate;
    }

    /**
     * Lock customer by ID.
     *
     * @param int $customerId
     *
     * @return void
     */
    public function execute(int $customerId): void
    {
        $customerSecure = $this->customerRegistry->retrieveSecureData($customerId);
        $customerSecure->setLockExpires('2030-12-31 00:00:00');
        $this->customerAuthUpdate->saveAuth($customerId);
    }
}
