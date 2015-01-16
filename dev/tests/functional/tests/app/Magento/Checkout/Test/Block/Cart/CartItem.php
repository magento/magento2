<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Client\Element\Locator;

/**
 * Class CartItem
 * Product item block on checkout page
 */
class CartItem extends AbstractCartItem
{
    /**
     * Selector for "Edit" button
     *
     * @var string
     */
    protected $edit = '.action.edit';

    /**
     * Selector for "Remove item" button
     *
     * @var string
     */
    protected $removeItem = '.action.delete';

    /**
     * Get bundle options
     *
     * @var string
     */
    protected $bundleOptions = './/dl[contains(@class, "item-options")]/dd[%d]/span[@class="price"][%d]';

    /**
     * 'Move to Wishlist' button
     *
     * @var string
     */
    protected $wishlistButton = '.towishlist';

    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $name = '.product-item-name a';

    /**
     * Cart item sub-total xpath selector
     *
     * @var string
     */
    protected $subtotalPrice = './/td[@class="col subtotal"]//*[@class="price-excluding-tax"]//span[@class="price"]';

    /**
     * Get product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->_rootElement->find($this->productName)->getText();
    }

    /**
     * Get product price
     *
     * @return string
     */
    public function getPrice()
    {
        $cartProductPrice = $this->_rootElement->find($this->price, Locator::SELECTOR_XPATH)->getText();
        return str_replace(',', '', $this->escapeCurrency($cartProductPrice));
    }

    /**
     * Get product price including tax
     *
     * @return string
     */
    public function getPriceInclTax()
    {
        $cartProductPrice = $this->_rootElement->find($this->priceInclTax, Locator::SELECTOR_XPATH)->getText();
        return str_replace(',', '', $this->escapeCurrency($cartProductPrice));
    }

    /**
     * Set product quantity
     *
     * @param int $qty
     * @return void
     */
    public function setQty($qty)
    {
        $this->_rootElement->find($this->qty, Locator::SELECTOR_XPATH)->setValue($qty);
    }

    /**
     * Get product quantity
     *
     * @return string
     */
    public function getQty()
    {
        return $this->_rootElement->find($this->qty, Locator::SELECTOR_XPATH)->getValue();
    }

    /**
     * Get sub-total for the specified item in the cart
     *
     * @return string
     */
    public function getSubtotalPrice()
    {
        $price = $this->_rootElement->find($this->subtotalPrice, Locator::SELECTOR_XPATH)->getText();
        return str_replace(',', '', $this->escapeCurrency($price));
    }

    /**
     * Get sub-total including tax for the specified item in the cart
     *
     * @return string
     */
    public function getSubtotalPriceInclTax()
    {
        $price = $this->_rootElement->find($this->subTotalPriceInclTax, Locator::SELECTOR_XPATH)->getText();
        return str_replace(',', '', $this->escapeCurrency($price));
    }

    /**
     * Get product options in the cart
     *
     * @return string
     */
    public function getOptions()
    {
        $optionsBlock = $this->_rootElement->find($this->optionsBlock, Locator::SELECTOR_XPATH);
        $options = [];

        if ($optionsBlock->isVisible()) {
            $titles = $optionsBlock->find('./dt', Locator::SELECTOR_XPATH)->getElements();
            $values = $optionsBlock->find('./dd', Locator::SELECTOR_XPATH)->getElements();

            foreach ($titles as $key => $title) {
                $value = $values[$key]->getText();
                $options[] = [
                    'title' => $title->getText(),
                    'value' => $this->escapeCurrencyForOption($value),
                ];
            }
        }

        return $options;
    }

    /**
     * Get product options name in the cart
     *
     * @return string
     */
    public function getOptionsName()
    {
        $optionsName = $this->_rootElement->find($this->optionsBlock . '//dt', Locator::SELECTOR_XPATH);
        if (!$optionsName->isVisible()) {
            return '';
        }
        return $optionsName->getText();
    }

    /**
     * Get product options value in the cart
     *
     * @return string
     */
    public function getOptionsValue()
    {
        $optionsValue = $this->_rootElement->find($this->optionsBlock . '//dd', Locator::SELECTOR_XPATH);
        if (!$optionsValue->isVisible()) {
            return '';
        }
        return $optionsValue->getText();
    }

    /**
     * Get item Bundle options
     *
     * @param int $index
     * @param int $itemIndex [optional]
     * @param string $currency [optional]
     * @return string
     */
    public function getPriceBundleOptions($index, $itemIndex = 1, $currency = '$')
    {
        $formatPrice = sprintf($this->bundleOptions, $index, $itemIndex);
        return trim($this->_rootElement->find($formatPrice, Locator::SELECTOR_XPATH)->getText(), $currency);
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_rootElement->find($this->name, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Edit product item in cart
     *
     * @return void
     */
    public function edit()
    {
        $this->_rootElement->find($this->edit)->click();
    }

    /**
     * Remove product item from cart
     *
     * @return void
     */
    public function removeItem()
    {
        $this->_rootElement->find($this->removeItem)->click();
    }

    /**
     * Escape currency in option label
     *
     * @param string $label
     * @return string
     */
    protected function escapeCurrencyForOption($label)
    {
        return preg_replace('/^(\d+) x (\w+) \W([\d\.,]+)$/', '$1 x $2 $3', $label);
    }

    /**
     * Click on move to wishlist button
     *
     * @return void
     */
    public function moveToWishlist()
    {
        $this->_rootElement->find($this->wishlistButton)->click();
    }

    /**
     * Check that edit button visible
     *
     * @return bool
     */
    public function isEditButtonVisible()
    {
        return $this->_rootElement->find($this->edit)->isVisible();
    }
}
