<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Customer account edit form.
 */
class CustomerForm extends Form
{
    /**
     * Save button button css selector.
     *
     * @var string
     */
    protected $saveButton = '[type="submit"]';

    /**
     * Locator for customer attribute on Edit Account Information page.
     *
     * @var string
     */
    protected $customerAttribute = "[name='%s[]']";

    /**
     * Validation text message for a field.
     *
     * @var string
     */
    protected $validationText = '.mage-error[for="%s"]';

    /**
     * Click on save button.
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->saveButton)->click();
    }

    /**
     * Fill the customer data.
     *
     * @param FixtureInterface $customer
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $customer, SimpleElement $element = null)
    {
        /** @var Customer $customer */
        if ($customer->hasData()) {
            parent::fill($customer, $element);
        }
        return $this;
    }

    /**
     * Get all error validation messages for fields.
     *
     * @param Customer $customer
     * @return array
     */
    public function getValidationMessages(Customer $customer)
    {
        $messages = [];
        foreach (array_keys($customer->getData()) as $field) {
            $element = $this->_rootElement->find(sprintf($this->validationText, str_replace('_', '-', $field)));
            if ($element->isVisible()) {
                $messages[$field] = $element->getText();
            }
        }

        return $messages;
    }
}
