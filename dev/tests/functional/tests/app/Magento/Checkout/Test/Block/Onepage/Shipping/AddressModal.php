<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Shipping;

use Magento\Mtf\Block\Form;

/**
 * Checkout shipping address modal block.
 */
class AddressModal extends Form
{
    /**
     * CSS Selector for Save button.
     *
     * @var string
     */
    private $saveButton = '.action-save-address';

    /**
     * Selector for field's error message.
     *
     * @var string
     */
    private $errorMessage = '.mage-error';

    /**
     * Selector for error fields.
     *
     * @var string
     */
    private $errorField = '._error';

    /**
     * Selector for field label that have error message.
     *
     * @var string
     */
    private $fieldLabel = '.label';

    /**
     * Click on 'Save Address' button.
     *
     * @return void
     */
    public function save()
    {
        $this->_rootElement->find($this->saveButton)->click();
    }

    /**
     * Get Error messages for attributes.
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $result = [];

        foreach ($this->_rootElement->getElements($this->errorField) as $item) {
            $result[$item->find($this->fieldLabel)->getText()] = $item->find($this->errorMessage)->getText();
        }

        return $result;
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
}
