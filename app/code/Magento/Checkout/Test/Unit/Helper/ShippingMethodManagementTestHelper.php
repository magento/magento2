<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Helper;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\ShippingMethodManagement;

/**
 * Test helper for shipping method management used in unit tests.
 *
 * Extends the concrete ShippingMethodManagement while bypassing
 * the parent constructor and stubbing public methods used by tests.
 */
class ShippingMethodManagementTestHelper extends ShippingMethodManagement
{
    public function __construct()
    {
        // Skip parent constructor to avoid dependency graph
    }
    /**
     * @inheritDoc
     */
    public function estimateByAddress($cartId, EstimateAddressInterface $address)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getList($cartId)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get($cartId)
    {
        return null;
    }
}
