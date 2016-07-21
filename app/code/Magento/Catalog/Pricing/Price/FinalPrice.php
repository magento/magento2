<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Final price model
 */
class FinalPrice extends AbstractPrice implements FinalPriceInterface
{
    /**
     * Price type final
     */
    const PRICE_CODE = 'final_price';

    /**
     * @var BasePrice
     */
    private $basePrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $minimalPrice;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountInterface
     */
    protected $maximalPrice;

    /**
     * Get Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        return max(0, $this->getBasePrice()->getValue());
    }

    /**
     * Get Minimal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMinimalPrice()
    {
        if (!$this->minimalPrice) {
            $minimalPrice = $this->product->getMinimalPrice();
            if ($minimalPrice === null) {
                $minimalPrice = $this->getValue();
            } else {
                $minimalPrice = $this->priceCurrency->convertAndRound($minimalPrice);
            }
            $this->minimalPrice = $this->calculator->getAmount($minimalPrice, $this->product);
        }
        return $this->minimalPrice;
    }

    /**
     * Get Maximal Price Amount
     *
     * @return \Magento\Framework\Pricing\Amount\AmountInterface
     */
    public function getMaximalPrice()
    {
        if (!$this->maximalPrice) {
            $maximalPrice = $this->product->getMaximalPrice();
            if ($maximalPrice === null) {
                $maximalPrice = $this->getValue();
            } else {
                $maximalPrice = $this->priceCurrency->convertAndRound($maximalPrice);
            }
            $this->maximalPrice = $this->calculator->getAmount($maximalPrice, $this->product);
        }
        return $this->maximalPrice;
    }

    /**
     * Retrieve base price instance lazily
     *
     * @return BasePrice|\Magento\Framework\Pricing\Price\PriceInterface
     */
    protected function getBasePrice()
    {
        if (!$this->basePrice) {
            $this->basePrice = $this->priceInfo->getPrice(BasePrice::PRICE_CODE);
        }
        return $this->basePrice;
    }
}
