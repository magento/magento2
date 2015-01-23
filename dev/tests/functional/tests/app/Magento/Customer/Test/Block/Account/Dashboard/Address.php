<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Account\Dashboard;

use Magento\Mtf\Block\Block;

/**
 * Class Address
 * Customer Dashboard Address Book block
 */
class Address extends Block
{
    /**
     * Default Billing Address Edit link
     *
     * @var string
     */
    protected $defaultBillingAddressEdit = '[data-ui-id=default-billing-edit-link]';

    /**
     * Shipping address block selector
     *
     * @var string
     */
    protected $shippingAddressBlock = '.box-shipping-address';

    /**
     * Billing address block selector
     *
     * @var string
     */
    protected $billingAddressBlock = '.box-billing-address';

    /**
     * Edit Default Billing Address
     *
     * @return void
     */
    public function editBillingAddress()
    {
        $this->_rootElement->find($this->defaultBillingAddressEdit)->click();
    }

    /**
     * Returns Default Billing Address Text
     *
     * @return array|string
     */
    public function getDefaultBillingAddressText()
    {
        return $this->_rootElement->find($this->billingAddressBlock)->getText();
    }

    /**
     * Returns Default Shipping Address Text
     *
     * @return array|string
     */
    public function getDefaultShippingAddressText()
    {
        return $this->_rootElement->find($this->shippingAddressBlock)->getText();
    }
}
