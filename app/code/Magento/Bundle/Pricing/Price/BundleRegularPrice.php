<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
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
     * @inheritdoc
     */
    public function getAmount()
    {
        $price = $this->getValue();
        $valueIndex = (string) $price;
        if (!isset($this->amount[$valueIndex])) {
            if ($this->product->getPriceType() == Price::PRICE_TYPE_FIXED) {
                /** @var \Magento\Catalog\Pricing\Price\CustomOptionPrice $customOptionPrice */
                $customOptionPrice = $this->priceInfo->getPrice(CustomOptionPrice::PRICE_CODE);
                $price += $customOptionPrice->getCustomOptionRange(true, $this->getPriceCode());
            }
            $this->amount[$valueIndex] = $this->calculator->getMinRegularAmount($price, $this->product);
        }
        return $this->amount[$valueIndex];
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
                $price += $customOptionPrice->getCustomOptionRange(false, $this->getPriceCode());
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
