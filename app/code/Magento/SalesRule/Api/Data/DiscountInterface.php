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
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function getDiscountData();

    /**
     * Get Rule Label
     *
     * @return mixed
     */
    public function getRuleLabel();

    /**
     * Get Rule ID
     *
     * @return string
     */
    public function getRuleID();
}
