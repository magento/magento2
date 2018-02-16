<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information
 * of a configurable product from the storefront.
 */
class Price extends \Magento\Catalog\Test\Block\Product\Price
{
    /**
     * A CSS selector for a Price label.
     *
     * @var string
     */
    protected $priceLabel = '.normal-price .price-label';

    /**
     * Mapping for different types of Price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'special_price' => [
            'selector' => '.normal-price .price'
        ]
    ];

    /**
     * This method returns the price represented by the block.
     *
     * @return SimpleElement
     */
    public function getPriceLabel()
    {
        return $this->_rootElement->find($this->priceLabel);
    }
}
