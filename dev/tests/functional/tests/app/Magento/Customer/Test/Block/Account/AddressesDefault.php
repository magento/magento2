<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Addresses default block
 *
 */
class AddressesDefault extends Block
{
    /**
     * Selector for change billing address
     *
     * @var string
     */
    protected $changeBillingAddressSelector = '.box-address-billing a';

    /**
     * Click on address book menu item
     */
    public function goToAddressBook()
    {
        $this->waitForElementVisible($this->changeBillingAddressSelector, Locator::SELECTOR_CSS);
        $this->_rootElement->find($this->changeBillingAddressSelector, Locator::SELECTOR_CSS)->click();
    }
}
