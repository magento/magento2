<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Bundle\Model\Product\Price;

/**
 * Bundle product regular price model
 */
class BundleRegularPrice extends \Magento\Catalog\Pricing\Price\RegularPrice implements RegularPriceInterface
{
    /**
     * @var BundleCalculatorInterface
     */
    protected $calculator;

    /**
     * @var AmountInterface
     */
    protected $maximalPrice;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param BundleCalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        BundleCalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
    }

    /**
     * @inheritdoc
     */
    public function getAmount()
    {
        if (!isset($this->amount[$this->getValue()])) {
            $price = $this->getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(true);
            }
            $this->amount[$this->getValue()] = $this->calculator->getMinRegularAmount($price, $this->product);
        }
        return $this->amount[$this->getValue()];
    }

    /**
     * Returns max price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        if (null === $this->maximalPrice) {
            $price = $this->getValue();
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(false);
            }
            $this->maximalPrice = $this->calculator->getMaxRegularAmount($price, $this->product);
        }
        return $this->maximalPrice;
    }

    /**
     * Returns min price
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        return $this->getAmount();
    }
}
