<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block;

use Exception;
use Magento\Checkout\Test\Block\Cart\CartItem;
use Magento\Checkout\Test\Block\Onepage\Link;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Cart
 * Shopping cart block
 */
class Cart extends Block
{
    // @codingStandardsIgnoreStart
    /**
     * Selector for cart item block
     *
     * @var string
     */
    protected $cartItemByProductName = './/tbody[contains(@class,"cart item") and (.//*[contains(@class,"product-item-name")]/a[.="%s"])]';
    // @codingStandardsIgnoreEnd

    /**
     * Proceed to checkout block
     *
     * @var string
     */
    protected $onepageLinkBlock = '.action.primary.checkout';

    /**
     * 'Clear Shopping Cart' button
     *
     * @var string
     */
    protected $clearShoppingCart = '#empty_cart_button';

    /**
     * 'Update Shopping Cart' button
     *
     * @var string
     */
    protected $updateShoppingCart = '[name="update_cart_action"]';

    /**
     * Cart empty block selector
     *
     * @var string
     */
    protected $cartEmpty = '.cart-empty';

    /**
     * Get cart item block
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
     * Get proceed to checkout block
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
     * Press 'Check out with PayPal' button
     *
     * @return void
     */
    public function paypalCheckout()
    {
        $this->_rootElement->find('[data-action=checkout-form-submit]', Locator::SELECTOR_CSS)->click();
    }

    /**
     * Returns the total discount price
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
     * Clear shopping cart
     *
     * @return void
     */
    public function clearShoppingCart()
    {
        $clearShoppingCart = $this->_rootElement->find($this->clearShoppingCart);
        if ($clearShoppingCart->isVisible()) {
            $clearShoppingCart->click();
        }
    }

    /**
     * Check if a product has been successfully added to the cart
     *
     * @param FixtureInterface $product
     * @return boolean
     */
    public function isProductInShoppingCart(FixtureInterface $product)
    {
        return $this->getCartItem($product)->isVisible();
    }

    /**
     * Update shopping cart
     *
     * @return void
     */
    public function updateShoppingCart()
    {
        $this->_rootElement->find($this->updateShoppingCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Check that cart is empty
     *
     * @return bool
     */
    public function cartIsEmpty()
    {
        return $this->_rootElement->find($this->cartEmpty, Locator::SELECTOR_CSS)->isVisible();
    }
}
