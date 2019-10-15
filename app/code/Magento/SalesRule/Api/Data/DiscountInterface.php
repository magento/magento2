<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * @api
 */
interface DiscountInterface
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
     * @return string
     */
    public function getRuleID();
}
