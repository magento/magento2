<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Multishipping checkout choose item addresses block.
 */
class Addresses extends Block
{
    /**
     * 'Enter New Address' button.
     *
     * @var string
     */
    protected $newAddress = '[data-role="add-new-address"]';

    /**
     * Locator value for "Go to Shipping Information" button.
     *
     * @var string
     */
    protected $continue = '[class*=continue][data-role="can-continue"]';

    /**
     * Add new customer address.
     *
     * @return void
     */
    public function addNewAddress()
    {
        $this->_rootElement->find($this->newAddress, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Select shipping addresses for products.
     *
     * @param array $bindings
     * @return void
     */
    public function selectAddresses($bindings)
    {
        foreach ($bindings as $key => $value) {
            $this->_rootElement->find(
                '//tr[.//a[text()="' . $key . '"]]//select[contains(@name,"[address]")]',
                Locator::SELECTOR_XPATH,
                'select'
            )->setValue($value);
        }
        $this->clickContinueButton();
    }

    /**
     * Click "Continue to Billing Information" button.
     *
     * @return void
     */
    public function clickContinueButton()
    {
        $this->_rootElement->find($this->continue)->click();
    }
}
