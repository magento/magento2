<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class View
 * View block on order's view page
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class View extends Block
{
    /**
     * Item block
     *
     * @var string
     */
    protected $itemBlock = '//*[@class="order-title" and contains(.,"%d")]';

    /**
     * Content block
     *
     * @var string
     */
    protected $content = '//following-sibling::div[contains(@class,"order-items")][1]';

    /**
     * Link xpath selector
     *
     * @var string
     */
    protected $link = '//*[contains(@class,"order-links")]//a[normalize-space(.)="%s"]';

    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotal = '.grand_total span';

    /**
     * Grand total including tax search mask
     *
     * @var string
     */
    protected $grandTotalInclTax = '.grand_total_incl span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotal = '.subtotal .amount span';

    /**
     * Subtotal excluding tax search mask
     *
     * @var string
     */
    protected $subtotalExclTax = '.subtotal_excl span';

    /**
     * Subtotal including tax search mask
     *
     * @var string
     */
    protected $subtotalInclTax = '.subtotal_incl span';

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
    protected $discount = '.discount span';

    /**
     * Shipping search mask
     *
     * @var string
     */
    protected $shippingExclTax = '.shipping span';

    /**
     * Shipping search mask
     *
     * @var string
     */
    protected $shippingInclTax = '.shipping_incl span';

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
     * Order items top pager selector.
     *
     * @var string
     */
    private $itemTopPagerSelector = '[data-block=order-items-pager-top]';

    /**
     * Order items bottom pager selector.
     *
     * @var string
     */
    private $itemBottomPagerSelector = '[data-block=order-items-pager-bottom]';

    /**
     * Get item block
     *
     * @param int $id [optional]
     * @return Items
     */
    public function getItemBlock($id = null)
    {
        $selector = ($id === null) ? $this->content : sprintf($this->itemBlock, $id) . $this->content;
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Order\Items',
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Open link by name
     *
     * @param string $name
     * @return void
     */
    public function openLinkByName($name)
    {
        $this->_rootElement->find(sprintf($this->link, $name), Locator::SELECTOR_XPATH)->click();
        sleep(3); // TODO: remove after resolving an issue with ajax on Frontend.
    }

    /**
     * Get Grand Total Text
     *
     * @return string
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
     * Get Item price excluding tax
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
     * Get Item price excluding tax
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
     * Get Item price excluding tax
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
     * Get Grand Total Text
     *
     * @return string|null
     */
    public function getGrandTotalInclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals
     *
     * @return string
     */
    public function getTax()
    {
        $tax = $this->_rootElement->find($this->tax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($tax);
    }

    /**
     * Get Tax text from Order Totals
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
     * @return string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text
     *
     * @return string
     */
    public function getSubtotalExclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalExclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text
     *
     * @return string
     */
    public function getSubtotalInclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalInclTax, Locator::SELECTOR_CSS)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Shipping Excluding tax price text
     *
     * @return string|null
     */
    public function getShippingInclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingInclTax, Locator::SELECTOR_CSS);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
    }

    /**
     * Get Shipping Including tax price text
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

    /**
     * Is order items top pager displayed.
     *
     * @return bool
     */
    public function isTopPagerDisplayed()
    {
        return $this->_rootElement->find($this->itemTopPagerSelector)->isVisible();
    }

    /**
     * Is order items bottom pager displayed.
     *
     * @return bool
     */
    public function isBottomPagerDisplayed()
    {
        return $this->_rootElement->find($this->itemBottomPagerSelector)->isVisible();
    }
}
