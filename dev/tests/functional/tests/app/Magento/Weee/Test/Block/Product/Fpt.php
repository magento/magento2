<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Weee\Test\Block\Product;

use Magento\Catalog\Test\Block\Product\Price;

/**
 * This class is used to access the fpt price from the storefront
 */
class Fpt extends Price
{
    /**
     * Mapping for different type of price
     *
     * @var array
     */
    protected $mapTypePrices = [
        'weee' => [
            'selector' => '[class="weee"] .price',
        ],
        'weee_total' => [
            'selector' => '[class="weee"] [data-label="Total"] .price',
        ],
    ];

    /**
     * Get fpt
     *
     * @param string $currency
     * @return string
     */
    public function getFpt($currency = '$')
    {
        return $this->getTypePrice('weee', $currency);
    }

    /**
     * Get fpt total
     *
     * @param string $currency
     * @return string
     */
    public function getFptTotal($currency = '$')
    {
        return $this->getTypePrice('weee_total', $currency);
    }
}
