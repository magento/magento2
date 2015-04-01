<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Test\Block\Product;

/**
 * Minimum Advertised Price block.
 */
class Map extends \Magento\Catalog\Test\Block\AbstractPriceBlock
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'actual_price' => [
            'selector' => '.actual-price .price',
        ],
        'old_price' => [
            'selector' => '.old-price .price-wrapper',
        ]
    ];

    /**
     * 'Add to Cart' button.
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * 'Close' button.
     *
     * @var string
     */
    protected $close = '.class="ui-dialog-buttonset .action.close';

    /**
     * Get actual Price value on frontend.
     *
     * @param string $currency
     * @return string|null
     */
    public function getActualPrice($currency = '$')
    {
        return $this->getTypePrice('actual_price', $currency);
    }

    /**
     * Get old Price value on frontend.
     *
     * @param string $currency
     * @return string|null
     */
    public function getOldPrice($currency = '$')
    {
        return $this->getTypePrice('old_price', $currency);
    }

    /**
     * Add product to shopping cart from MAP Block.
     *
     * @return void
     */
    public function addToCart()
    {
        $this->_rootElement->find($this->addToCart)->click();
    }

    /**
     * Close MAP Block.
     *
     * @return void
     */
    public function close()
    {
        $this->_rootElement->find($this->close)->click();
        $this->waitForElementNotVisible($this->close);
    }
}
