<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information
 * of a configurable product from the storefront.
=======
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * This class is used to access the price related information of a configurable product from the storefront.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class Price extends \Magento\Catalog\Test\Block\Product\Price
{
    /**
     * A CSS selector for a Price label.
     *
     * @var string
     */
<<<<<<< HEAD
    protected $priceLabel = '.normal-price .price-label';
=======
    private $priceLabel = '.normal-price .price-label';
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

    /**
     * Mapping for different types of Price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'special_price' => [
<<<<<<< HEAD
            'selector' => '.normal-price .price'
        ]
    ];

    /**
     * This method returns the price represented by the block.
=======
            'selector' => '.normal-price .price',
        ],
    ];

    /**
     * This method returns the price label represented by the block.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @return SimpleElement
     */
    public function getPriceLabel()
    {
        return $this->_rootElement->find($this->priceLabel);
    }
}
