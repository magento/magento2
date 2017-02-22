<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Block\Cart;

use Magento\Checkout\Test\Block\Cart\Sidebar\Item;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Mini shopping cart block.
 */
class Sidebar extends Block
{
    /**
     * Quantity input selector.
     *
     * @var string
     */
    protected $qty = '//*[@class="product"]/*[@title="%s"]/following-sibling::*//*[contains(@class,"item-qty")]';

    /**
     * Mini cart link selector.
     *
     * @var string
     */
    protected $cartLink = 'a.showcart';

    /**
     * Mini cart content selector.
     *
     * @var string
     */
    protected $cartContent = 'div.block-minicart';

    /**
     * Product list in mini shopping cart.
     *
     * @var string
     */
    protected $cartProductList = './/*[contains(@role, "dialog") and not(contains(@style,"display: none;"))]';

    /**
     * Selector for cart item block.
     *
     * @var string
     */
    protected $cartProductName = '//*[@id="mini-cart"]//li[.//a[normalize-space(text())="%s"]]';

    /**
     * Counter qty locator.
     *
     * @var string
     */
    protected $counterQty = '.minicart-wrapper .counter.qty';

    /**
     * Locator value for Mini Shopping Cart wrapper.
     *
     * @var string
     */
    protected $counterNumberWrapper = '.minicart-wrapper';

    /**
     * Loading masc.
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Open mini cart.
     *
     * @return void
     */
    public function openMiniCart()
    {
        $this->waitCounterQty();
        if (!$this->_rootElement->find($this->cartContent)->isVisible()) {
            $this->_rootElement->find($this->cartLink)->click();
        }
    }

    /**
     * Wait counter qty visibility.
     *
     * @return void
     */
    protected function waitCounterQty()
    {
        $browser = $this->browser;
        $selector = $this->counterQty;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $counterQty = $browser->find($selector);
                return $counterQty->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get product quantity.
     *
     * @param string $productName
     * @return string
     */
    public function getProductQty($productName)
    {
        $this->openMiniCart();
        $productQty = sprintf($this->qty, $productName);
        return $this->_rootElement->find($productQty, Locator::SELECTOR_XPATH)->getValue();
    }

    /**
     * Get cart item block.
     *
     * @param FixtureInterface $product
     * @return Item
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
                sprintf($this->cartProductList . $this->cartProductName, $product->getName()),
                Locator::SELECTOR_XPATH
            );
            $cartItem = $this->blockFactory->create(
                'Magento\Checkout\Test\Block\Cart\Sidebar\Item',
                ['element' => $cartItemBlock]
            );
        }

        return $cartItem;
    }

    /**
     * Wait for init minicart.
     *
     * @return void
     */
    public function waitInit()
    {
        $browser = $this->browser;
        $selector = $this->counterNumberWrapper;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $counterQty = $browser->find($selector);
                return $counterQty->isVisible() ? true : null;
            }
        );
    }

    /**
     * Wait for loader is not visible.
     *
     * @return void
     */
    public function waitLoader()
    {
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
