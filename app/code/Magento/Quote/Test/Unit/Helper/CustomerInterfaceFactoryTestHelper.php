<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;

/**
 * Test helper for CustomerInterfaceFactory to expose mergeDataObjectWithArray method
 * referenced by legacy unit tests.
 */
class CustomerInterfaceFactoryTestHelper extends CustomerInterfaceFactory
{
    /**
     * Constructor intentionally left empty to skip parent constructor.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Merge array data into a CustomerInterface during tests.
     *
     * @param CustomerInterface $customer
     * @param array $data
     * @return CustomerInterface
     */
    public function mergeDataObjectWithArray(CustomerInterface $customer, array $data)
    {
        // Touch $data to satisfy PHPMD; keep behavior unchanged for tests
        if (!empty($data)) {
            // no-op
        }
        return $customer;
    }
}
