<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * @api
 * @since 100.0.2
 */
interface DiscountInterface
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty);

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule);
}
