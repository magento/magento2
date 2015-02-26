<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order billing address block.
 */
class Address extends Form
{
    /**
     * Selector for existing customer addresses dropdown.
     *
     * @var string
     */
    protected $existingAddressSelector = '#order-billing_address_customer_address_id';

    /**
     * 'Save in address book' checkbox.
     *
     * @var string
     */
    protected $saveInAddressBook = '#order-billing_address_save_in_address_book';

    /**
     * Get existing customer addresses.
     *
     * @return array
     */
    public function getExistingAddresses()
    {
        return explode("\n", $this->_rootElement->find($this->existingAddressSelector)->getText());
    }

    /**
     * Check the 'Save in address book' checkbox in billing address.
     *
     * @param string $saveAddress
     * @return void
     */
    public function saveInAddressBookBillingAddress($saveAddress)
    {
        $this->_rootElement->find($this->saveInAddressBook, Locator::SELECTOR_CSS, 'checkbox')->setValue($saveAddress);
    }
}
