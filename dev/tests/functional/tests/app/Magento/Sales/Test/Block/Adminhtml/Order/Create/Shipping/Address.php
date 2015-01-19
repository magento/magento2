<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class ShippingAddress
 * Adminhtml sales order create shipping address block
 */
class Address extends Form
{
    /**
     * 'Same as billing address' checkbox
     *
     * @var string
     */
    protected $sameAsBilling = '#order-shipping_same_as_billing';

    /**
     * Shipping address title selector
     *
     * @var string
     */
    protected $title = 'legend';

    /**
     * Check the 'Same as billing address' checkbox in shipping address
     *
     * @return void
     */
    public function setSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->title)->click();
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }

    /**
     * Uncheck the 'Same as billing address' checkbox in shipping address
     *
     * @return void
     */
    public function uncheckSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->title)->click();
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->setValue('No');
    }
}
