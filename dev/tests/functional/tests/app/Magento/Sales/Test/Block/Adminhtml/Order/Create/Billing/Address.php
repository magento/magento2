<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Billing;

use Mtf\Block\Form;

/**
 * Class BillingAddress
 * Adminhtml sales order billing address block
 *
 */
class Address extends Form
{
    /**
     * Selector for existing customer addresses dropdown
     *
     * @var string
     */
    protected $existingAddressSelector = '#order-billing_address_customer_address_id';

    /**
     * Get existing customer addresses
     *
     * @return array
     */
    public function getExistingAddresses()
    {
        $this->reinitRootElement();
        return explode("\n", $this->_rootElement->find($this->existingAddressSelector)->getText());
    }
}
