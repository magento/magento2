<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Form;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

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
    protected $customerAttribute = "[orig-name='%s[]']";

    /**
     * Validation text message for a field.
     *
     * @var string
     */
    protected $validationText = '.mage-error[for="%s"]';

    /**
     * Fixture mapping and fields revision.
     *
     * @param array|null $fields
     * @param string|null $parent
     * @return array
     */
    protected function dataMapping(array $fields = null, $parent = null)
    {
        if (isset($fields['website_id'])) {
            unset($fields['website_id']);
        }
        return parent::dataMapping($fields, $parent);
    }

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

    /**
     * Get Customer first name from field.
     *
     * @return string
     */
    public function getFirstName()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['customer']['value']['firstname']['selector']
        )->getValue();
    }

    /**
     * Get Customer last name from field.
     *
     * @return string
     */
    public function getLastName()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['customer']['value']['lastname']['selector']
        )->getValue();
    }

    /**
     * Set 'Change Email' checkbox value.
     *
     * @param boolean $value
     * @return void
     */
    public function setChangeEmail($value)
    {
        $mapping = $this->dataMapping();
        $this->_rootElement->find(
            $mapping['change_email']['selector'],
            $mapping['change_email']['strategy'],
            'checkbox'
        )->setValue($value ? "Yes" : "No");
    }

    /**
     * Set 'Change Password' checkbox value.
     *
     * @param boolean $value
     * @return void
     */
    public function setChangePassword($value)
    {
        $mapping = $this->dataMapping();
        $this->_rootElement->find(
            $mapping['change_password']['selector'],
            $mapping['change_password']['strategy'],
            'checkbox'
        )->setValue($value ? "Yes" : "No");
    }

    /**
     * Check if Current Password field is visible.
     *
     * @return boolean
     */
    public function isCurrentPasswordVisible()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['customer']['value']['current_password']['selector']
        )->isVisible();
    }

    /**
     * Check if Password field is visible.
     *
     * @return boolean
     */
    public function isPasswordVisible()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['customer']['value']['password']['selector']
        )->isVisible();
    }

    /**
     * Check if Confirmation field is visible.
     *
     * @return boolean
     */
    public function isConfirmPasswordVisible()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['confirmation']['selector'],
            $mapping['confirmation']['strategy']
        )->isVisible();
    }

    /**
     * Check if Email field is visible.
     *
     * @return boolean
     */
    public function isEmailVisible()
    {
        $mapping = $this->dataMapping();
        return $this->_rootElement->find(
            $mapping['customer']['value']['email']['selector']
        )->isVisible();
    }
}
