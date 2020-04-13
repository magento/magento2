<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

/**
 * Rule discount Interface
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
