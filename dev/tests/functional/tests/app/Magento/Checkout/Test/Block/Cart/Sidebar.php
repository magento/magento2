<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Mini cart subtotal selector.
     *
     * @var string
     */
    private $subtotal = '.subtotal .price';

    /**
     * Mini cart link selector.
     *
     * @var string
     */
    protected $cartLink = 'a.showcart';

    /**
     * Locator value for "Check out with Braintree PayPal" button.
     *
     * @var string
     */
    protected $braintreePaypalCheckoutButton = 'button[id^="braintree-paypal-mini-cart"]';

    /**
     * Locator value for "Proceed to Checkout" button.
     *
     * @var string
     */
    private $proceedToCheckoutButton = '#top-cart-btn-checkout';

    /**
     * Minicart items quantity
     *
     * @var string
     */
    protected $productCounter = './/*[@class="counter-number"]';

    /**
     * @var string
     */
    protected $visibleProductCounter = './/*[@class="items-total"]';

    /**
     * Empty minicart message
     *
     * @var string
     */
    protected $emptyCartMessage = './/*[@id="minicart-content-wrapper"]//*[@class="subtitle empty"]';

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
     * Loading mask.
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
        if (!$this->browser->find($this->cartContent)->isVisible()) {
            $this->browser->find($this->cartLink)->click();
        }
        // Need this because there are a lot of JS processes that update shopping cart items
        // and we cant control them all
        sleep(5);
    }

    /**
     * Click "Check out with Braintree PayPal" button.
     *
     * @return void
     */
    public function clickBraintreePaypalButton()
    {
        // Button can be enabled/disabled few times.
        sleep(3);

        $windowsCount = count($this->browser->getWindowHandles());
        $this->_rootElement->find($this->braintreePaypalCheckoutButton)
            ->click();
        $browser = $this->browser;
        $this->browser->waitUntil(
            function () use ($browser, $windowsCount) {
                return count($browser->getWindowHandles()) === ($windowsCount + 1) ? true: null;
            }
        );
    }

    /**
     * Click "Proceed to Checkout" button.
     *
     * @return void
     */
    public function clickProceedToCheckoutButton()
    {
        $this->_rootElement->find($this->proceedToCheckoutButton)->click();
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
     * Get empty minicart message.
     *
     * @return string
     */
    public function getEmptyMessage()
    {
        $this->_rootElement->find($this->cartLink)->click();
        return $this->_rootElement->find($this->emptyCartMessage, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Is minicart items quantity block visible.
     *
     * @return bool
     */
    public function isItemsQtyVisible()
    {
        return $this->_rootElement->find($this->productCounter, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Get qty of items in minicart.
     *
     * @return int
     */
    public function getItemsQty()
    {
        return (int)$this->_rootElement->find($this->productCounter, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Returns message with count of visible items
     *
     * @return string
     */
    public function getVisibleItemsCounter()
    {
        return $this->_rootElement->find($this->visibleProductCounter, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get subtotal.
     *
     * @return string
     */
    public function getSubtotal()
    {
        $this->openMiniCart();
        $subtotal = $this->_rootElement->find($this->subtotal)->getText();

        return $this->escapeCurrency($subtotal);
    }

    /**
     * Get cart item block.
     *
     * @param FixtureInterface $product
     * @return Item
     */
    public function getCartItem(FixtureInterface $product)
    {
        $this->openMiniCart();
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
                \Magento\Checkout\Test\Block\Cart\Sidebar\Item::class,
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

    /**
     * Escape currency in price.
     *
     * @param string $price
     * @param string $currency [optional]
     * @return string
     */
    protected function escapeCurrency($price, $currency = '$')
    {
        return str_replace($currency, '', $price);
    }
}
