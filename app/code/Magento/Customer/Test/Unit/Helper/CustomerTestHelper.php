<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Helper;

use Magento\Customer\Model\Customer;

/**
 * Test helper for Customer model to expose getDefaultBilling for PHPUnit 12 mocks.
 */
class CustomerTestHelper extends Customer
{
    /**
     * Constructor intentionally empty to skip parent dependencies.
     */
    public function __construct()
    {
    }

    /**
     * Get default billing id stored in test data.
     *
     * @return mixed
     */
    public function getDefaultBilling()
    {
        return $this->getData('default_billing');
    }
}
