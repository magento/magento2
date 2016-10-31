<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Cart totals block.
 */
class Totals extends Block
{
    /**
     * Grand total search mask.
     *
     * @var string
     */
    protected $grandTotal = '.grand.totals .price';

    /**
     * Grand total search mask.
     *
     * @var string
     */
    protected $grandTotalExclTax = '.totals.grand.excl span';

    /**
     * Grand total search mask.
     *
     * @var string
     */
    protected $grandTotalInclTax = '.totals.grand.incl span';

    /**
     * Subtotal search mask.
     *
     * @var string
     */
    protected $subtotal = '.totals.sub .price';

    /**
     * Subtotal search mask.
     *
     * @var string
     */
    protected $subtotalExclTax = '.totals.sub.excl .price';

    /**
     * Subtotal search mask.
     *
     * @var string
     */
    protected $subtotalInclTax = '.totals.sub.incl .price';

    /**
     * Tax search mask.
     *
     * @var string
     */
    protected $tax = '.totals-tax span';

    /**
     * Get shipping price selector.
     *
     * @var string
     */
    protected $shippingPriceSelector = '.shipping.excl .price';

    /**
     * Get discount.
     *
     * @var string
     */
    protected $discount = '[class=totals] .amount .price';

    /**
     * Get shipping price including tax selector.
     *
     * @var string
     */
    protected $shippingPriceInclTaxSelector = '.shipping.incl .price';

    /**
     * Get shipping price block selector.
     *
     * @var string
     */
    protected $shippingPriceBlockSelector = '.totals.shipping.excl';

    /**
     * Block wait element.
     *
     * @var string
     */
    protected $blockWaitElement = '.loading-mask';

    /**
     * Get Grand Total Text.
     *
     * @return string
     */
    public function getGrandTotal()
    {
        $this->waitForUpdatedTotals();
        $grandTotal = $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total Text.
     *
     * @return string|null
     */
    public function getGrandTotalIncludingTax()
    {
        $priceElement = $this->_rootElement->find($this->grandTotalInclTax, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Get Grand Total Text.
     *
     * @return string|null
     */
    public function getGrandTotalExcludingTax()
    {
        $priceElement = $this->_rootElement->find($this->grandTotalExclTax, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Get Tax text from Order Totals.
     *
     * @return string|null
     */
    public function getTax()
    {
        $priceElement = $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Check that Tax is visible.
     *
     * @return bool
     */
    public function isTaxVisible()
    {
        return $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get Subtotal text.
     *
     * @return string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text.
     *
     * @return string|null
     */
    public function getSubtotalIncludingTax()
    {
        $priceElement = $this->_rootElement->find($this->subtotalInclTax, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Get Subtotal text.
     *
     * @return string|null
     */
    public function getSubtotalExcludingTax()
    {
        $priceElement = $this->_rootElement->find($this->subtotalExclTax, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Method that escapes currency symbols.
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }

    /**
     * Get discount.
     *
     * @return string|null
     */
    public function getDiscount()
    {
        $this->waitForElementNotVisible($this->blockWaitElement);
        $this->waitForElementVisible($this->discount, Locator::SELECTOR_CSS);
        $priceElement = $this->_rootElement->find($this->discount, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Get shipping price.
     *
     * @return string|null
     */
    public function getShippingPrice()
    {
        $priceElement = $this->_rootElement->find($this->shippingPriceSelector, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Get shipping price.
     *
     * @return string|null
     */
    public function getShippingPriceInclTax()
    {
        $priceElement = $this->_rootElement->find($this->shippingPriceInclTaxSelector, Locator::SELECTOR_CSS);
        return $priceElement->isVisible() ? $this->escapeCurrency($priceElement->getText()) : null;
    }

    /**
     * Is visible shipping price block.
     *
     * @return bool
     */
    public function isVisibleShippingPriceBlock()
    {
        return $this->_rootElement->find($this->shippingPriceBlockSelector, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Wait for totals block to update contents asynchronously.
     *
     * @return void
     */
    public function waitForUpdatedTotals()
    {
        // Code under may use JavaScript delay at this point as well.
        sleep(1);
        $this->waitForElementNotVisible($this->blockWaitElement);
    }

    /**
     * Wait for shipping block to appear
     *
     * @return bool|null
     */
    public function waitForShippingPriceBlock()
    {
        $this->waitForElementVisible($this->shippingPriceBlockSelector, Locator::SELECTOR_CSS);
    }
}
