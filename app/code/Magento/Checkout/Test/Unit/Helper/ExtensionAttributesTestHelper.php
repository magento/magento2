<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

/**
 * Test helper for Quote extension attributes stub that exposes
 * get/setShippingAssignments with simple in-memory storage.
 */
class ExtensionAttributesTestHelper
{
    /** @var array<int, mixed>|null */
    private $shippingAssignments = null;

    /**
     * Returns current shipping assignments.
     *
     * @return array<int, mixed>|null
     */
    public function getShippingAssignments()
    {
        return $this->shippingAssignments;
    }

    /**
     * Sets shipping assignments.
     *
     * @param array<int, mixed>|null $assignments
     * @return $this
     */
    public function setShippingAssignments($assignments)
    {
        $this->shippingAssignments = $assignments;
        return $this;
    }
}
