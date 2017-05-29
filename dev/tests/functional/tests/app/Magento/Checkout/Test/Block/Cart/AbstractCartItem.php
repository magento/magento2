<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Magento\Mtf\Block\Block;

/**
 * Base product item block on checkout page.
 */
class AbstractCartItem extends Block
{
    /**
     * Selector for product name.
     *
     * @var string
     */
    protected $productName = '.product-item-name > a';

    /**
     * Selector for unit price.
     *
     * @var string
     */
    protected $price = './/td[@class="col price"]//span[@class="price"]';

    /**
     * Selector for unit price including tax.
     *
     * @var string
     */
    protected $priceInclTax = './/td[@class="col price"]/*[@class="price-including-tax"]/span';

    /**
     * Selector for unit price excluding tax.
     *
     * @var string
     */
    protected $priceExclTax = './/td[@class="col price"]/*[@class="price-excluding-tax"]/span';

    /**
     * Quantity input selector.
     *
     * @var string
     */
    protected $qty = './/input[@data-role="cart-item-qty"]';

    /**
     * Cart item sub-total xpath selector.
     *
     * @var string
     */
    protected $subtotalPrice = '.col.subtotal .price';

    /**
     * Cart item sub-total excluding tax xpath selector.
     *
     * @var string
     */
    protected $subTotalPriceExclTax = '.col.subtotal .price-excluding-tax .price';

    /**
     * Cart item sub-total including tax xpath selector.
     *
     * @var string
     */
    protected $subTotalPriceInclTax = '.col.subtotal .price-including-tax .price';

    /**
     *  Selector for options block.
     *
     * @var string
     */
    protected $optionsBlock = './/dl[contains(@class, "item-options")]';

    /**
     * Escape currency in price.
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
