<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Block\Cart;

use Magento\Checkout\Test\Block\Cart\Sidebar\Item;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Sidebar
 * Mini shopping cart block
 */
class Sidebar extends Block
{
    /**
     * Quantity input selector
     *
     * @var string
     */
    protected $qty = '//*[@class="product"]/*[@title="%s"]/following-sibling::*//*[@class="value qty"]';

    /**
     * Mini cart link selector
     *
     * @var string
     */
    protected $cartLink = 'a.showcart';

    /**
     * Mini cart content selector
     *
     * @var string
     */
    protected $cartContent = 'div.minicart';

    /**
     * Selector for cart item block
     *
     * @var string
     */
    protected $cartItemByProductName = './/*[contains(@class,"products minilist")]//li[.//a[.="%s"]]';

    /**
     * Counter qty locator
     *
     * @var string
     */
    protected $counterQty = './/span[@class="counter qty"]';

    /**
     * Open mini cart
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
     * Wait counter qty visibility
     *
     * @return void
     */
    protected function waitCounterQty()
    {
        $browser = $this->browser;
        $selector = $this->counterQty;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $counterQty = $browser->find($selector, Locator::SELECTOR_XPATH);
                return $counterQty->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get product quantity
     *
     * @param string $productName
     * @return string
     */
    public function getProductQty($productName)
    {
        $this->openMiniCart();
        $productQty = sprintf($this->qty, $productName);
        return $this->_rootElement->find($productQty, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get cart item block
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
                sprintf($this->cartItemByProductName, $product->getName()),
                Locator::SELECTOR_XPATH
            );
            $cartItem = $this->blockFactory->create(
                'Magento\Checkout\Test\Block\Cart\Sidebar\Item',
                ['element' => $cartItemBlock]
            );
        }

        return $cartItem;
    }
}
