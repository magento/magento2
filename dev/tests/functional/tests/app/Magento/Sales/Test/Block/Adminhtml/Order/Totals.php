<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Invoice totals block
 */
class Totals extends Block
{
    /**
     * Grand total search mask.
     *
     * @var string
     */
    protected $grandTotal = '//tr[normalize-space(td)="Grand Total"]//span';

    /**
     * Grand total excluding tax search mask.
     *
     * @var string
     */
    protected $grandTotalExclTax = '//tr[normalize-space(td)="Grand Total (Excl.Tax)"]//span';

    /**
     * Grand total including tax search mask.
     *
     * @var string
     */
    protected $grandTotalInclTax = '//tr[normalize-space(td)="Grand Total (Incl.Tax)"]//span';

    /**
     * Subtotal search mask.
     *
     * @var string
     */
    protected $subtotal = '//tr[normalize-space(td)="Subtotal"]//span';

    /**
     * Subtotal excluding tax search mask.
     *
     * @var string
     */
    protected $subtotalExclTax = '//tr[normalize-space(td)="Subtotal (Excl.Tax)"]//span';

    /**
     * Subtotal including tax search mask.
     *
     * @var string
     */
    protected $subtotalInclTax = '//tr[normalize-space(td)="Subtotal (Incl.Tax)"]//span';

    /**
     * Tax search mask.
     *
     * @var string
     */
    protected $tax = '//tr[normalize-space(td)="Tax"]//span';

    /**
     * Discount search mask.
     *
     * @var string
     */
    protected $discount = '//tr[normalize-space(td)="Discount"]//span';

    /**
     * Shipping excluding tax search mask.
     *
     * @var string
     */
    protected $shippingExclTax = '//tr[contains (.,"Shipping") and contains (.,"(Excl.Tax)")]//span';

    /**
     * Shipping including tax search mask.
     *
     * @var string
     */
    protected $shippingInclTax = '//tr[contains (.,"Shipping") and contains (.,"(Incl.Tax)")]//span';

    /**
     * Get Grand Total Text.
     *
     * @return string
     */
    public function getGrandTotal()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total Excluding Tax Text.
     *
     * @return string
     */
    public function getGrandTotalExclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalExclTax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Grand Total Including Tax Text.
     *
     * @return string
     */
    public function getGrandTotalInclTax()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotalInclTax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals.
     *
     * @return string
     */
    public function getTax()
    {
        $tax = $this->_rootElement->find($this->tax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($tax);
    }

    /**
     * Get Tax text from Order Totals.
     *
     * @return string|null
     */
    public function getDiscount()
    {
        $discount = $this->_rootElement->find($this->discount, Locator::SELECTOR_XPATH);
        return $discount->isVisible() ? $this->escapeCurrency($discount->getText()) : null;
    }

    /**
     * Get Subtotal text.
     *
     * @return string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text.
     *
     * @return string
     */
    public function getSubtotalExclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalExclTax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Subtotal text.
     *
     * @return string
     */
    public function getSubtotalInclTax()
    {
        $subTotal = $this->_rootElement->find($this->subtotalInclTax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Get Shipping Excluding tax price text.
     *
     * @return string|null
     */
    public function getShippingInclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingInclTax, Locator::SELECTOR_XPATH);
        return $subTotal->isVisible() ? $this->escapeCurrency($subTotal->getText()) : null;
    }

    /**
     * Get Shipping Including tax price text.
     *
     * @return string|null
     */
    public function getShippingExclTax()
    {
        $subTotal = $this->_rootElement->find($this->shippingExclTax, Locator::SELECTOR_XPATH);
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
