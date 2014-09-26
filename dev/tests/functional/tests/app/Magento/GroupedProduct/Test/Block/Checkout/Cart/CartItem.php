<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GroupedProduct\Test\Block\Checkout\Cart;

use Magento\Checkout\Test\Block\Cart\AbstractCartItem;
use Magento\Checkout\Test\Block\Cart\CartItem as CheckoutCartItem;

/**
 * Class CartItem
 * Product item block on checkout page
 */
class CartItem extends AbstractCartItem
{
    /**
     * Get product name
     *
     * @return string
     */
    protected function getProductName()
    {
        $this->_rootElement->find($this->productName)->getText();
    }

    /**
     * Get product price
     *
     * @return string
     */
    public function getPrice()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getPrice();
        }

        return $result;
    }

    /**
     * Set product quantity
     *
     * @param array $data
     * @return void
     */
    public function setQty(array $data)
    {
        foreach ($data as $productSku => $qty) {
            /** @var CheckoutCartItem $cartItem */
            $cartItem = $this->config['associated_cart_items'][$productSku];
            $cartItem->setQty($qty);
        }
    }

    /**
     * Get product quantity
     *
     * @return string
     */
    public function getQty()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getQty();
        }

        return $result;
    }

    /**
     * Get sub-total for the specified item in the cart
     *
     * @return string
     */
    public function getSubtotalPrice()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getSubtotalPrice();
        }

        return $result;
    }

    /**
     * Get product options in the cart
     *
     * @return string
     */
    public function getOptions()
    {
        $result = [];
        foreach ($this->config['associated_cart_items'] as $productSku => $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[$productSku] = $cartItem->getOptions();
        }

        return $result;
    }

    /**
     * Remove associated products items from cart
     *
     * @return void
     */
    public function removeItem()
    {
        foreach ($this->config['associated_cart_items'] as $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $cartItem->reinitRootElement();
            $cartItem->removeItem();
        }
    }
}
