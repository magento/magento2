<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory as DiscountDataFactory;
use Magento\SalesRule\Model\Validator;

abstract class AbstractDiscount implements DiscountInterface
{
    /**
     * @var DiscountDataFactory
     */
    protected $discountFactory;

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        protected readonly Validator $validator,
        DiscountDataFactory $discountDataFactory,
        protected readonly PriceCurrencyInterface $priceCurrency
    ) {
        $this->discountFactory = $discountDataFactory;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return DiscountData
     */
    abstract public function calculate($rule, $item, $qty);

    /**
     * @param float $qty
     * @param Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        return $qty;
    }
}
