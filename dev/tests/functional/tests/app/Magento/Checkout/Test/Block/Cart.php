<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block;

use Exception;
use Magento\Checkout\Test\Block\Cart\CartItem;
use Magento\Checkout\Test\Block\Onepage\Link;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Cart
 * Shopping Cart block
 */
class Cart extends Block
{
    // @codingStandardsIgnoreStart
    /**
     * Locator value for correspondent "Shopping Cart item" block.
     *
     * @var string
     */
    protected $cartItemByProductName = './/tbody[contains(@class,"cart item") and (.//*[contains(@class,"product-item-name")]/a[.="%s"])]';
    // @codingStandardsIgnoreEnd

    /**
     * Locator value for "Proceed to One Page Checkout" block.
     *
     * @var string
     */
    protected $onepageLinkBlock = '.action.primary.checkout';

    /**
     * Locator value for "Clear Shopping Cart" button.
     *
     * @var string
     */
    protected $clearShoppingCart = '#empty_cart_button';

    /**
     * Locator value for "Update Shopping Cart" button.
     *
     * @var string
     */
    protected $updateShoppingCart = '.update[name="update_cart_action"]';

    /**
     * Locator value for "Check out with PayPal" button.
     *
     * @var string
     */
    protected $paypalCheckoutButton = '[data-action=checkout-form-submit]';

    /**
     * Locator value for "empty Shopping Cart" block.
     *
     * @var string
     */
    protected $cartEmpty = '.cart-empty';

    /**
     * Locator value for "Shopping Cart" container.
     *
     * @var string
     */
    protected $cartContainer = '.cart-container';

    /**
     * Locator value for "Remove Product" button.
     *
     * @var string
     */
    protected $deleteItemButton = 'a.action.action-delete';

    /**
     * Get Shopping Cart item.
     *
     * @param FixtureInterface $product
     * @return CartItem
     */
    public function getCartItem(FixtureInterface $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
        $cartItem = null;

        if ($this->hasRender($typeId)) {
            $cartItem = $this->callRender($typeId, 'getCartItem', ['product' => $product]);
        } else {
            $cartItemBlock = $this->_rootElement->find(
                sprintf($this->cartItemByProductName, $product->getName()),
                Locator::SELECTOR_XPATH
            );
            $cartItem = $this->blockFactory->create(
                'Magento\Checkout\Test\Block\Cart\CartItem',
                ['element' => $cartItemBlock]
            );
        }

        return $cartItem;
    }

    /**
     * Get "Proceed to One Page Checkout" block.
     *
     * @return Link
     */
    public function getOnepageLinkBlock()
    {
        return Factory::getBlockFactory()->getMagentoCheckoutOnepageLink(
            $this->_rootElement->find($this->onepageLinkBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Click "Check out with PayPal" button.
     *
     * @return void
     */
    public function paypalCheckout()
    {
        $this->_rootElement->find($this->paypalCheckoutButton)->click();
    }

    /**
     * Get total discount Price value.
     *
     * @return string
     * @throws Exception
     */
    public function getDiscountTotal()
    {
        $element = $this->_rootElement->find(
            '//table[@id="shopping-cart-totals-table"]' .
            '//tr[@class="totals"]' .
            '//td[@class="amount"]//span[@class="price"]',
            Locator::SELECTOR_XPATH
        );
        if (!$element->isVisible()) {
            throw new Exception('Error could not find the Discount Total in the HTML');
        }
        return $element->getText();
    }

    /**
     * Clear Shopping Cart.
     *
     * @return void
     */
    public function clearShoppingCart()
    {
        while (!$this->cartIsEmpty()) {
            $this->_rootElement->find($this->deleteItemButton)->click();
        }
    }

    /**
     * Check if Product is present in Shopping Cart or not.
     *
     * @param FixtureInterface $product
     * @return boolean
     */
    public function isProductInShoppingCart(FixtureInterface $product)
    {
        return $this->getCartItem($product)->isVisible();
    }

    /**
     * Update Shopping Cart.
     *
     * @return void
     */
    public function updateShoppingCart()
    {
        $this->_rootElement->find($this->updateShoppingCart)->click();
    }

    /**
     * Check if Shopping Cart is empty or not.
     *
     * @return bool
     */
    public function cartIsEmpty()
    {
        return $this->_rootElement->find($this->cartEmpty)->isVisible();
    }

    /**
     * Wait while Shopping Cart container is loaded.
     *
     * @return void
     */
    public function waitCartContainerLoading()
    {
        $this->waitForElementVisible($this->cartContainer);
    }
}
