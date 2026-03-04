<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

/**
 * Rule discount Interface
 * @api
 */
interface RuleDiscountInterface
{
    /**
     * Get Discount Data
     *
     * @return \Magento\SalesRule\Api\Data\DiscountDataInterface
     */
    public function getDiscountData();

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel();

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID();
}
