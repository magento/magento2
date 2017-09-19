<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for information about addresses on order page.
 */
class Addresses extends Block
{
    /**
     * Billing address.
     *
     * @var string
     */
    private $billingAddress = '.order-billing-address address';

    /**
     * New address button selector.
     *
     * @var string
     */
    private $newAddressButton = '.action-show-popup';

    /**
     * Shipping address.
     *
     * @var string
     */
    private $shippingAddress = '.order-shipping-address address';

    /**
     * Get customer's billing address from the data inside block.
     *
     * @return string
     */
    public function getCustomerBillingAddress()
    {
        return $this->_rootElement->find($this->billingAddress, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get customer's shipping address from the data inside block.
     *
     * @return string
     */
    public function getCustomerShippingAddress()
    {
        return $this->_rootElement->find($this->shippingAddress, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Checks if new address button is visible.
     *
     * @return bool
     */
    public function isNewAddressButtonVisible()
    {
        $button = $this->_rootElement->find($this->newAddressButton);
        return $button->isVisible();
    }

    /**
     * Clicks new address button.
     *
     * @return void
     */
    public function clickNewAddressButton()
    {
        $this->_rootElement->find($this->newAddressButton)->click();
    }
}
