<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Block\Address;

use Magento\Customer\Test\Fixture\Address;
use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class Edit
 * Customer address edit block
 */
class Edit extends Form
{
    /**
     * 'Save address' button
     *
     * @var string
     */
    protected $saveAddress = '[data-action=save-address]';

    /**
     * VAT field selector
     *
     * @var string
     */
    protected $vatFieldId = 'vat_id';

    /**
     * Edit customer address
     *
     * @param Address $fixture
     */
    public function editCustomerAddress(Address $fixture)
    {
        $this->fill($fixture);
        $this->saveAddress();
    }

    /**
     * Save new VAT id
     *
     * @param $vat
     */
    public function saveVatID($vat)
    {
        $this->_rootElement->find($this->vatFieldId, Locator::SELECTOR_ID)->setValue($vat);
        $this->saveAddress();
    }

    /**
     * Click on save address button
     *
     * @return void
     */
    public function saveAddress()
    {
        $this->_rootElement->find($this->saveAddress)->click();
    }
}
