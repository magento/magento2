<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Block;

/**
 * Class AbstractCartItem
 * Base product item block on checkout page
 */
class AbstractCartItem extends Block
{
    /**
     * Selector for product name
     *
     * @var string
     */
    protected $productName = '.product-item-name > a';

    /**
     * Selector for unit price
     *
     * @var string
     */
    protected $price = './/td[@class="col price"]/*[@class="price-excluding-tax"]/span';

    /**
     * Selector for unit price including tax
     *
     * @var string
     */
    protected $priceInclTax = './/td[@class="col price"]/*[@class="price-including-tax"]/span';

    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $qty = './/input[@type="number" and @title="Qty"]';

    /**
     * Cart item sub-total xpath selector
     *
     * @var string
     */
    protected $subtotalPrice = './/td[@class="col subtotal"]//*[@class="price-excluding-tax"]//span[@class="price"]';

    // @codingStandardsIgnoreStart
    /**
     * Cart item sub-total including tax xpath selector
     *
     * @var string
     */
    protected $subTotalPriceInclTax = '//td[@class="col subtotal"]//*[@class="price-including-tax"]//span[@class="price"]';
    // @codingStandardsIgnoreEnd

    /**
     *  Selector for options block
     *
     * @var string
     */
    protected $optionsBlock = './/dl[@class="item-options"]';

    /**
     * Escape currency in price
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
