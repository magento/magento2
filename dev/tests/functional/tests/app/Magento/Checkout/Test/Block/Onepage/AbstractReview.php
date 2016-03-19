<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * One page checkout abstract review block.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractReview extends Block
{
    /**
     * Product item block locator.
     *
     * @var string
     */
    protected $productItemByName = './/li[contains(@class,"product-item") and contains(.,"%s")]';

    /**
     * Grand total search mask.
     *
     * @var string
     */
    protected $grandTotal = '[class="grand totals"] td>strong';

    /**
     * Grand total excluding tax search mask.
     *
     * @var string
     */
    protected $grandTotalExclTax = '.grand.totals.excl .price';

    /**
     * Grand total including tax search mask.
     *
     * @var string
     */
    protected $grandTotalInclTax = '.grand.totals.incl .price';

    /**
     * Subtotal search mask.
     *
     * @var string
     */
    protected $subtotal = '.totals.sub .price';

    /**
     * Subtotal excluding tax search mask.
     *
     * @var string
     */
    protected $subtotalExclTax = '.totals.sub.excl .price';

    /**
     * Subtotal including tax search mask.
     *
     * @var string
     */
    protected $subtotalInclTax = '.totals.sub.incl .price';

    /**
     * Tax search mask.
     *
     * @var string
     */
    protected $tax = '.totals-tax .price';

    /**
     * Discount search mask.
     *
     * @var string
     */
    protected $discount = '.totals.discount .price';

    /**
     * Shipping excluding tax search mask.
     *
     * @var string
     */
    protected $shippingExclTax = '[class="totals shipping excl"] span';

    /**
     * Shipping including tax search mask.
     *
     * @var string
     */
    protected $shippingInclTax = '.totals.shipping.incl .price';

    /**
     * Product price excluding tax search mask.
     *
     * @var string
     */
    protected $itemExclTax = '.price-excluding-tax .price';

    /**
     * Product price including tax search mask.
     *
     * @var string
     */
    protected $itemInclTax = '.price-including-tax .price';

    // @codingStandardsIgnoreStart
    /**
     * Product price subtotal excluding tax search mask.
     *
     * @var string
     */
    protected $itemSubExclTax = '.subtotal .price-excluding-tax .price';

    /**
     * Product price subtotal including tax search mask.
     *
     * @var string
     */
    protected $itemSubInclTax = '.subtotal .price-including-tax .price';
    // @codingStandardsIgnoreEnd

    /**
     * Wait element.
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
     * Get Grand Total Text.
     *
     * @return array|string
     */
    public function getGrandTotal()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotal)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Item price excluding tax.
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceExclTax($productName)
    {
        $productItem = $this->_rootElement->find(
            sprintf($this->productItemByName, $productName),
            Locator::SELECTOR_XPATH
        );
        $price = $productItem->find($this->itemExclTax);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price including tax.
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemPriceInclTax($productName)
    {
        $productItem = $this->_rootElement->find(
            sprintf($this->productItemByName, $productName),
            Locator::SELECTOR_XPATH
        );
        $price = $productItem->find($this->itemInclTax);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item subtotal price excluding tax.
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubExclTax($productName)
    {
        $productItem = $this->_rootElement->find(
            sprintf($this->productItemByName, $productName),
            Locator::SELECTOR_XPATH
        );
        $price = $productItem->find($this->itemSubExclTax);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item subtotal price excluding tax.
     *
     * @param string $productName
     * @return string|null
     */
    public function getItemSubInclTax($productName)
    {
        $productItem = $this->_rootElement->find(
            sprintf($this->productItemByName, $productName),
            Locator::SELECTOR_XPATH
        );
        $price = $productItem->find($this->itemSubInclTax);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Grand Total excluding tax text.
     *
     * @return string
     */
    public function getGrandTotalExclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalExclTax)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total including tax text.
     *
     * @return string
     */
    public function getGrandTotalInclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalInclTax)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals.
     *
     * @return string|null
     */
    public function getTax()
    {
        $tax = $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS);
        return $tax->isVisible() ? $this->escapeCurrency($tax->getText()) : null;
    }

    /**
     * Get Discount text from Order Totals.
     *
     * @return string|null
     */
    public function getDiscount()
    {
        $discount = $this->_rootElement->find($this->discount);
        return $discount->isVisible() ? $this->escapeCurrency($discount->getText()) : null;
    }

    /**
     * Get Subtotal text.
     *
     * @return array|string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal excluding tax text.
     *
     * @return string
     */
    public function getSubtotalExclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalExclTax)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal including tax text.
     *
     * @return string
     */
    public function getSubtotalInclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalInclTax)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Shipping including tax price text.
     *
     * @return string|null
     */
    public function getShippingInclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingInclTax);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
    }

    /**
     * Get Shipping excluding tax price text.
     *
     * @return string|null
     */
    public function getShippingExclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingExclTax);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
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
}
