<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Api\Data\CartExtension;

/**
 * Test helper for CartExtension to provide setShippingAssignments() for unit tests.
 */
class CartExtensionTestHelper extends CartExtension
{
    /** @var array */
    private array $testData = [];

    /**
     * Set shipping assignments for tests.
     *
     * @param array $shippingAssignments
     * @return $this
     */
    public function setShippingAssignments($shippingAssignments)
    {
        $this->testData['shipping_assignments'] = $shippingAssignments;
        return $this;
    }

    /**
     * Get shipping assignments for tests.
     *
     * @return array|null
     */
    public function getShippingAssignments()
    {
        return $this->testData['shipping_assignments'] ?? null;
    }
}
