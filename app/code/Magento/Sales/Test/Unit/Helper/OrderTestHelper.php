<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Sales\Model\Order;

/**
 * Test helper for Sales Order to expose addAddresses() and setAddresses() for tests.
 */
class OrderTestHelper extends Order
{
    public function __construct()
    {
    }

    /**
     * No-op method to satisfy tests that expect chaining.
     *
     * @return $this
     */
    public function addAddresses()
    {
        return $this;
    }

    /**
     * No-op method to satisfy tests that expect chaining.
     *
     * @param array $addresses
     * @return $this
     */
    public function setAddresses($addresses)
    {
        // Store to avoid unused parameter warnings and aid test introspection
        $this->setData('addresses', $addresses);
        return $this;
    }
}
