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
     * @var int
     */
    private $callCount = 0;

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

    /**
     * Get store ID with call counter
     *
     * Custom logic: Returns 1 on first call, 2 on second call
     * This is used to test different store access scenarios
     *
     * @return int
     */
    public function getStoreId()
    {
        $this->callCount++;
        // Return store1 on first call, store2 on second call
        return $this->callCount === 1 ? 1 : 2;
    }

    /**
     * Load customer
     *
     * Custom logic: Sets ID via parent's setId() without requiring a resource (for testing)
     * Parent AbstractModel::load() requires a resource to be set
     *
     * @param mixed $id
     * @param mixed $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($id, $field = null)
    {
        parent::setId($id);
        return $this;
    }
}
