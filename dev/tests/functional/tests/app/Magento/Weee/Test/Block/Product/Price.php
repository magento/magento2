<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Product;

/**
 * This class is used to access the fpt price from the storefront.
 */
class Price extends \Magento\Catalog\Test\Block\AbstractPriceBlock
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'regular_price' => [
            'selector' => '.price-wrapper',
        ],
        'fpt_price' => [
            'selector' => '[data-price-type="weee"] .price',
        ],
        'final_price' => [
            'selector' => '[data-label="Final Price"] .price',
        ],
    ];

    /**
     * Get regular price.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPrice($currency = '$')
    {
        return $this->getTypePrice('regular_price', $currency);
    }

    /**
     * Get fpt.
     *
     * @param string $currency
     * @return string|null
     */
    public function getFptPrice($currency = '$')
    {
        return $this->getTypePrice('fpt_price', $currency);
    }

    /**
     * Get final price.
     *
     * @param string $currency
     * @return string|null
     */
    public function getFinalPrice($currency = '$')
    {
        return $this->getTypePrice('final_price', $currency);
    }
}
