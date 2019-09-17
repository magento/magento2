<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\Quote\Model\Quote;

/**
 * Aggregate cart level discounts
 *
 * @package Magento\QuoteGraphQl\Model\Cart
 */
class DiscountAggregator
{
    /**
     * @var DataFactory
     */
    private $discountFactory;

    /**
     * @param DataFactory|null $discountDataFactory
     */
    public function __construct(
        DataFactory $discountDataFactory
    ) {
        $this->discountFactory = $discountDataFactory;
    }

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
                        /* @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $ruleDiscount */
                        $ruleDiscount = $this->discountFactory->create();
                        /* @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
                        $discountData = $discountPerRule[$key]['discount'];
                        $ruleDiscount->setAmount($discountData->getAmount()+$discount->getAmount());
                        $ruleDiscount->setBaseAmount($discountData->getBaseAmount()+$discount->getBaseAmount());
                        $ruleDiscount->setOriginalAmount(
                            $discountData->getOriginalAmount()+$discount->getOriginalAmount()
                        );
                        $ruleDiscount->setBaseOriginalAmount(
                            $discountData->getBaseOriginalAmount()+$discount->getBaseOriginalAmount()
                        );
                        $discountPerRule[$key]['discount'] = $ruleDiscount;
                        $discountPerRule[$key]['rule'] = $rule;
                    } else {
                        $discountPerRule[$key]['discount'] = $discount;
                        $discountPerRule[$key]['rule'] = $rule;
                    }
                }
            }
        }
        return $discountPerRule;
    }
}
