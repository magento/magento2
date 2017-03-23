<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Test\Block\Cart\CartItem;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Product item fpt block on cart page
 */
class Fpt extends Block
{
    /**
     * Selector for price
     *
     * @var string
     */
    protected $price = './/*[@class="price"]';

    /**
     * Selector for fpt
     *
     * @var string
     */
    protected $fpt = './/*[@class="cart-tax-info"]/*[@class="weee"]/span';

    /**
     * Selector for fpt total
     *
     * @var string
     */
    protected $fptTotal = './/*[@class="cart-tax-total"]/*[@class="weee"]/span';

    /**
     * Get product fpt
     *
     * @return string
     */
    public function getFpt()
    {
        $cartProductFpt = $this->_rootElement->find($this->fpt, Locator::SELECTOR_XPATH);
        if (!$cartProductFpt->isVisible()) {
            $this->_rootElement->find($this->price, Locator::SELECTOR_XPATH)->click();
        }
        return str_replace(',', '', $this->escapeCurrency($cartProductFpt->getText()));
    }

    /**
     * Get product fpt total
     *
     * @return string
     */
    public function getFptTotal()
    {
        $cartProductFptTotal = $this->_rootElement->find($this->fptTotal, Locator::SELECTOR_XPATH);
        $cartProductFptTotalText = $cartProductFptTotal->isVisible() ? $cartProductFptTotal->getText() : '';
        return str_replace(',', '', $this->escapeCurrency($cartProductFptTotalText));
    }

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
