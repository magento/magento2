<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Review
 * One page checkout status review block
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Review extends Block
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#review-buttons-container button';

    /**
     * Centinel authentication block
     *
     * @var string
     */
    protected $centinelBlock = '#centinel-authenticate-block';

    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotal = '[class="grand totals"] span';

    /**
     * Grand total excluding tax search mask
     *
     * @var string
     */
    protected $grandTotalExclTax = '[class="grand totals excl"] span';

    /**
     * Grand total including tax search mask
     *
     * @var string
     */
    protected $grandTotalInclTax = '[class="grand totals incl"] span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotal = '.totals.sub .price';

    /**
     * Subtotal excluding tax search mask
     *
     * @var string
     */
    protected $subtotalExclTax = '[class="totals sub excl"] span';

    /**
     * Subtotal including tax search mask
     *
     * @var string
     */
    protected $subtotalInclTax = '[class="totals sub incl"] span';

    /**
     * Tax search mask
     *
     * @var string
     */
    protected $tax = '.totals-tax span';

    /**
     * Discount search mask
     *
     * @var string
     */
    protected $discount = '[class="totals"] .amount>span';

    /**
     * Shipping excluding tax search mask
     *
     * @var string
     */
    protected $shippingExclTax = '[class="totals shipping excl"] span';

    /**
     * Shipping including tax search mask
     *
     * @var string
     */
    protected $shippingInclTax = '[class="totals shipping incl"] span';

    /**
     * Product price excluding tax search mask
     *
     * @var string
     */
    protected $itemExclTax = '//tr[contains (.,"%s")]/td[@class="col price"]/span[@class="price-excluding-tax"]/span';

    /**
     * Product price including tax search mask
     *
     * @var string
     */
    protected $itemInclTax = '//tr[contains (.,"%s")]/td[@class="col price"]/span[@class="price-including-tax"]/span';

    // @codingStandardsIgnoreStart
    /**
     * Product price subtotal excluding tax search mask
     *
     * @var string
     */
    protected $itemSubExclTax = '//tr[contains (.,"%s")]/td[@class="col subtotal"]/span[@class="price-excluding-tax"]/span';

    /**
     * Product price subtotal including tax search mask
     *
     * @var string
     */
    protected $itemSubInclTax = '//tr[contains (.,"%s")]/td[@class="col subtotal"]/span[@class="price-including-tax"]/span';
    // @codingStandardsIgnoreEnd

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * @constructor
     * @param SimpleElement $element
     * @param BlockFactory $blockFactory
     * @param BrowserInterface $browser
     */
    public function __construct(SimpleElement $element, BlockFactory $blockFactory, BrowserInterface $browser)
    {
        parent::__construct($element, $blockFactory, $browser);
    }

    /**
     * Fill billing address
     *
     * @return void
     */
    public function placeOrder()
    {
        $this->_rootElement->find($this->continue, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Wait for 3D Secure card validation
     *
     * @return void
     */
    public function waitForCardValidation()
    {
        $this->waitForElementNotVisible($this->centinelBlock);
    }

    /**
     * Get Grand Total Text
     *
     * @return array|string
     */
    public function getGrandTotal()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Item price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceExclTax($productName)
    {
        $locator = sprintf($this->itemExclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price including tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceInclTax($productName)
    {
        $locator = sprintf($this->itemInclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item subtotal price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubExclTax($productName)
    {
        $locator = sprintf($this->itemSubExclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item subtotal price excluding tax
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubInclTax($productName)
    {
        $locator = sprintf($this->itemSubInclTax, $productName);
        $price = $this->_rootElement->find($locator, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Grand Total excluding tax text
     *
     * @return string
     */
    public function getGrandTotalExclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalExclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total including tax text
     *
     * @return string
     */
    public function getGrandTotalInclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals
     *
     * @return array|string
     */
    public function getTax()
    {
        $tax = $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($tax);
    }

    /**
     * Get Discount text from Order Totals
     *
     * @return string|null
     */
    public function getDiscount()
    {
        $discount = $this->_rootElement->find($this->discount, Locator::SELECTOR_CSS);
        return $discount->isVisible() ? $this->escapeCurrency($discount->getText()) : null;
    }

    /**
     * Get Subtotal text
     *
     * @return array|string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal excluding tax text
     *
     * @return string
     */
    public function getSubtotalExclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalExclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal including tax text
     *
     * @return string
     */
    public function getSubtotalInclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Shipping including tax price text
     *
     * @return string|null
     */
    public function getShippingInclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingInclTax, Locator::SELECTOR_CSS);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
    }

    /**
     * Get Shipping excluding tax price text
     *
     * @return string|null
     */
    public function getShippingExclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingExclTax, Locator::SELECTOR_CSS);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
