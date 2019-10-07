<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Model\Quote;

/**
 * Aggregate cart level discounts
 *
 * @package Magento\QuoteGraphQl\Model\Cart
 */
class DiscountAggregator
{
    /**
     * Aggregate Discount per rule
     *
     * @param Quote $quote
     * @return array
     */
    public function aggregateDiscountPerRule(
        Quote $quote
    ) {
        $items = $quote->getItems();
        $discountPerRule = [];
        foreach ($items as $item) {
            $discountBreakdown = $item->getExtensionAttributes()->getDiscounts();
            if ($discountBreakdown) {
                foreach ($discountBreakdown as $key => $value) {
                    /* @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discount */
                    $discount = $value['discount'];
                    $rule = $value['rule'];
                    if (isset($discountPerRule[$key])) {
                        $discountPerRule[$key]['discount'] += $discount->getAmount();
                    } else {
                        $discountPerRule[$key]['discount'] = $discount->getAmount();
                    }
                    $discountPerRule[$key]['rule'] = $rule;
                }
            }
        }
        return $discountPerRule;
    }
}
