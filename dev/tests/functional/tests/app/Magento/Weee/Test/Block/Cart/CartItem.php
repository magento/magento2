<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Cart;

use Magento\Mtf\Client\Locator;

/**
 * Product item fpt block on cart page
 */
class CartItem extends \Magento\Checkout\Test\Block\Cart\CartItem
{
    /**
     * Fpt price block selector
     *
     * @var string
     */

    protected $priceFptBlock = './/td[@class="col price"]';

    /**
     * Fpt subtotal block selector
     *
     * @var string
     */
    protected $subtotalFptBlock = './/td[@class="col subtotal"]';

    /**
     * Get block price fpt
     *
     * @return \Magento\Weee\Test\Block\Cart\CartItem\Fpt
     */
    public function getPriceFptBlock()
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Cart\CartItem\Fpt',
            ['element' => $this->_rootElement->find($this->priceFptBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get block subtotal fpt
     *
     * @return \Magento\Weee\Test\Block\Cart\CartItem\Fpt
     */
    public function getSubtotalFptBlock()
    {
        return $this->blockFactory->create(
            'Magento\Weee\Test\Block\Cart\CartItem\Fpt',
            ['element' => $this->_rootElement->find($this->subtotalFptBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
