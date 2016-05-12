<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Addresses default block.
 *
 */
class AddressesDefault extends Block
{
    /**
     * Content of default address block.
     *
     * @var string
     */
    protected $defaultAddressContent = '.block-content';

    /**
     * Billing address block selector.
     *
     * @var string
     */
    protected $billingAddressBlock = '.box.box-address-billing .box-content';

    /**
     * Shipping address block selector.
     *
     * @var string
     */
    protected $shippingAddressBlock = '.box.box-address-shipping .box-content';

    /**
     * Selector for change billing address.
     *
     * @var string
     */
    protected $changeBillingAddressSelector = '.box-address-billing a';

    /**
     * Click on address book menu item.
     */
    public function goToAddressBook()
    {
        $this->waitForElementVisible($this->changeBillingAddressSelector, Locator::SELECTOR_CSS);
        $this->_rootElement->find($this->changeBillingAddressSelector, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get block text.
     *
     * @return string
     */
    public function getBlockText()
    {
        return $this->_rootElement->find($this->defaultAddressContent)->getText();
    }

    /**
     * Returns Default Billing Address Text.
     *
     * @return string
     */
    public function getDefaultBillingAddress()
    {
        return $this->_rootElement->find($this->billingAddressBlock)->getText();
    }

    /**
     * Returns Default Shipping Address Text.
     *
     * @return string
     */
    public function getDefaultShippingAddress()
    {
        return $this->_rootElement->find($this->shippingAddressBlock)->getText();
    }
}
