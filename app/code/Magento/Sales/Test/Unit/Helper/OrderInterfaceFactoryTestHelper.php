<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Helper;

use Magento\Sales\Api\Data\OrderInterfaceFactory;

/**
 * Test helper for OrderInterfaceFactory to expose populate() for tests.
 */
class OrderInterfaceFactoryTestHelper extends OrderInterfaceFactory
{
    /**
     * @var mixed
     */
    private $lastPopulateOrder;

    public function __construct()
    {
        // Skip parent dependencies
    }

    /**
     * No-op populate used in tests to set expectations.
     *
     * @param mixed $baseOrder
     * @return $this
     */
    public function populate($baseOrder)
    {
        // Store to avoid unused parameter warnings and aid test introspection
        $this->lastPopulateOrder = $baseOrder;
        return $this;
    }
}
