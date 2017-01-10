<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Checkout\Cart;

use Magento\Checkout\Test\Block\Cart\AbstractCartItem;
use Magento\Checkout\Test\Block\Cart\CartItem as CheckoutCartItem;
use Magento\Mtf\Client\Locator;

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
        foreach ($this->config['associated_cart_items'] as $cartItem) {
            /** @var CheckoutCartItem $cartItem */
            $result[] = [
                'title' => $cartItem->getProductName(),
                'value' => $cartItem->getQty(),
            ];
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
            $cartItem->removeItem();
        }
    }

    /**
     * Get product price including tax
     *
     * @return string|null
     */
    public function getPriceInclTax()
    {
        return $this->getPriceByType($this->priceInclTax, Locator::SELECTOR_XPATH);
    }

    /**
     * Get product price excluding tax
     *
     * @return string|null
     */
    public function getPriceExclTax()
    {
        return $this->getPriceByType($this->priceExclTax, Locator::SELECTOR_XPATH);
    }

    /**
     * Get sub-total excluding tax for the specified item in the cart
     *
     * @return string|null
     */
    public function getSubtotalPriceExclTax()
    {
        return $this->getPriceByType($this->subTotalPriceExclTax);
    }

    /**
     * Get price for the specified item in the cart by the price type
     *
     * @return string|null
     */
    public function getSubtotalPriceInclTax()
    {
        return $this->getPriceByType($this->subTotalPriceInclTax);
    }

    /**
     * @param string $priceType
     * @param string $strategy
     * @return mixed|null
     */
    private function getPriceByType($priceType, $strategy = Locator::SELECTOR_CSS)
    {
        $cartProductPrice = $this->_rootElement->find($priceType, $strategy);
        return $cartProductPrice->isVisible()
            ? str_replace(',', '', $this->escapeCurrency($cartProductPrice->getText()))
            : null;
    }
}
