<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Math;

/**
 * Calculations Library
 *
 * @api
 * @since 2.0.0
 */
class Calculator
{
    /**
     * Delta collected during rounding steps
     *
     * @var float
     * @since 2.0.0
     */
    protected $_delta = 0.0;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|null
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     * @since 2.0.0
     */
    public function deltaRound($price, $negative = false)
    {
        $roundedPrice = $price;
        if ($roundedPrice) {
            if ($negative) {
                $this->_delta = -$this->_delta;
            }
            $price += $this->_delta;
            $roundedPrice = $this->priceCurrency->round($price);
            $this->_delta = $price - $roundedPrice;
            if ($negative) {
                $this->_delta = -$this->_delta;
            }
        }
        return $roundedPrice;
    }
}
