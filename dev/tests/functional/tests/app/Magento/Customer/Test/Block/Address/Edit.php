<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Address;

use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

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
     * Locator for address simple (input, textarea, not multiple fields) attribute
     *
     * @var string
     */
    private $addressSimpleAttribute = "[name='%s']";

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

    /**
     * Fixture mapping.
     *
     * @param array|null $fields
     * @param string|null $parent
     * @return array
     */
    protected function dataMapping(array $fields = null, $parent = null)
    {
        if (isset($fields['custom_attribute'])) {
            $this->placeholders = ['attribute_code' => $fields['custom_attribute']['code']];
            $this->applyPlaceholders();
        }
        return parent::dataMapping($fields, $parent);
    }

    /**
     * Check if Customer Address Simple(input, textarea, not multiple fields) Attribute visible
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAddressSimpleAttributeVisible($attributeCode)
    {
        return $this->_rootElement->find(sprintf($this->addressSimpleAttribute, $attributeCode))->isVisible();
    }
}
