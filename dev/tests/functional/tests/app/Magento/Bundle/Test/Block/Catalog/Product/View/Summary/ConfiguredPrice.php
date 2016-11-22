<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Summary;

use Magento\Catalog\Test\Block\AbstractPriceBlock;

/**
 * This class is used to access the price related information from the storefront.
 */
class ConfiguredPrice extends AbstractPriceBlock
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'configured_price' => [
            'selector' => '.price',
        ]
    ];

    /**
     * This method returns the price represented by the block.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPrice($currency = '$')
    {
        return $this->getTypePrice('configured_price', $currency);
    }
}
