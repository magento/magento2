<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information of a configurable product from the storefront.
=======

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information
 * of a configurable product from the storefront.
>>>>>>> upstream/2.2-develop
 */
class Price extends \Magento\Catalog\Test\Block\Product\Price
{
    /**
     * A CSS selector for a Price label.
     *
     * @var string
     */
<<<<<<< HEAD
    private $priceLabel = '.normal-price .price-label';
=======
    protected $priceLabel = '.normal-price .price-label';
>>>>>>> upstream/2.2-develop

    /**
     * Mapping for different types of Price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'special_price' => [
<<<<<<< HEAD
            'selector' => '.normal-price .price',
        ],
    ];

    /**
     * This method returns the price label represented by the block.
=======
            'selector' => '.normal-price .price'
        ]
    ];

    /**
     * This method returns the price represented by the block.
>>>>>>> upstream/2.2-develop
     *
     * @return SimpleElement
     */
    public function getPriceLabel()
    {
        return $this->_rootElement->find($this->priceLabel);
    }
}
