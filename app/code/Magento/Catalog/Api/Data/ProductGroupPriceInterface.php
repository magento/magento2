<?php
/**
 * Group Price
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

interface ProductGroupPriceInterface
{
    /**
     * Retrieve customer group id
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set customer group id
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue();

    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     */
    public function setValue($value);
}
