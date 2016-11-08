<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Block\Catalog\Product;

/**
 * This class is used to access the price related information from the storefront.
 */
class CustomizedPrice extends \Magento\Catalog\Test\Block\AbstractPriceBlock
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'final_price' => [
            'selector' => '.price',
        ]
    ];

    /**
     * This method returns the price represented by the block.
     *
     * @param string $currency
     * @return string|null
     */
    public function getFinalPrice($currency = '$')
    {
        return $this->getTypePrice('final_price', $currency);
    }
}
