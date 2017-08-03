<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * Class \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
 *
 * @since 2.0.0
 */
abstract class AbstractDiscount implements DiscountInterface
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     * @since 2.0.0
     */
    protected $discountFactory;

    /**
     * @var \Magento\SalesRule\Model\Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @since 2.0.0
     */
    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->validator = $validator;
        $this->discountFactory = $discountDataFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     * @since 2.0.0
     */
    abstract public function calculate($rule, $item, $qty);

    /**
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     * @since 2.0.0
     */
    public function fixQuantity($qty, $rule)
    {
        return $qty;
    }
}
