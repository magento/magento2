<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Locator value for "Check out with PayPal" button.
     *
     * @var string
     */
    protected $inContextPaypalCheckoutButton = 'ul.checkout-methods-items a[data-action="paypal-express-in-context-checkout"]';

    /**
     * Locator value for "Check out with Braintree PayPal" button.
     *
     * @var string
     */
    protected $braintreePaypalCheckoutButton = './/button[contains(@id, "braintree-paypal-mini-cart")]';

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
     * PayPal load spinner.
     *
     * @var string
     */
    protected $preloaderSpinner = '#preloaderSpinner';

    /**
     * Cart item class name.
     *
     * @var string
     */
    protected $cartItemClass = \Magento\Checkout\Test\Block\Cart\CartItem::class;

    /**
     * Wait for PayPal page is loaded.
     *
     * @return void
     */
    public function waitForFormLoaded()
    {
        $this->waitForElementNotVisible($this->preloaderSpinner);
    }

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
                $this->cartItemClass,
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
     * Click "Check out with Braintree PayPal" button.
     *
     * @return string
     */
    public function braintreePaypalCheckout()
    {
        $currentWindow = $this->browser->getCurrentWindow();
        $this->_rootElement->find($this->braintreePaypalCheckoutButton, Locator::SELECTOR_XPATH)
            ->click();
        return $currentWindow;
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
     * Click "Check out with PayPal" button.
     */
    public function inContextPaypalCheckout()
    {
        $this->waitForCheckoutButton();
        $this->_rootElement->find($this->inContextPaypalCheckoutButton)->click();
        $this->browser->selectWindow();
        $this->waitForFormLoaded();
        $this->browser->closeWindow();
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

    /**
     * Wait until in-context checkout button is visible.
     *
     * @return void
     */
    public function waitForCheckoutButton()
    {
        $this->waitForElementVisible($this->inContextPaypalCheckoutButton);
    }


}
