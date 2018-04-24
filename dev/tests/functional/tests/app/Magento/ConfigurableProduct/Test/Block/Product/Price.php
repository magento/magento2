<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information of a configurable product from the storefront.
 */
class Price extends \Magento\Catalog\Test\Block\Product\Price
{
    /**
     * A CSS selector for a Price label.
     *
     * @var string
     */
    private $priceLabel = '.normal-price .price-label';

    /**
     * Mapping for different types of Price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'special_price' => [
            'selector' => '.normal-price .price',
        ],
    ];

    /**
     * This method returns the price label represented by the block.
     *
     * @return SimpleElement
     */
    public function getPriceLabel()
    {
        return $this->_rootElement->find($this->priceLabel);
    }
}
