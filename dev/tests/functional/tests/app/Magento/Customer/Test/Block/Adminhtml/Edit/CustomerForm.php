<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Form for creation of the customer.
 */
class CustomerForm extends FormTabs
{
    /**
     * Magento form loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * Customer form to load.
     *
     * @var string
     */
    protected $activeFormTab = '.entry-edit.form-inline [data-bind="visible: active"]:not([style="display: none;"])';

    /**
     * Field wrapper with label on form.
     *
     * @var string
     */
    protected $fieldLabel = './/*[contains(@class, "form__field")]/*[contains(@class,"label")]';

    /**
     * Field wrapper with absent label on form.
     *
     * @var string
     */
    protected $fieldLabelAbsent = './/*[contains(@class, "form__field") and not(./*[contains(@class,"label")]/*)]';

    /**
     * Field wrapper with control block on form.
     *
     * @var string
     */
    protected $fieldWrapperControl = './/*[contains(@class, "form__field")]/*[contains(@class,"control")]';

    // @codingStandardsIgnoreStart
    /**
     * Field wrapper with absent control block on form.
     *
     * @var string
     */
    protected $fieldWrapperControlAbsent = './/*[contains(@class, "form__field") and not(./input or ./*[contains(@class,"control")]/*)]';
    // @codingStandardsIgnoreEnd

    /**
     * Fill Customer forms on tabs by customer, addresses data.
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function fillCustomer(FixtureInterface $customer, $address = null)
    {
        $this->waitForm();
        $this->waitFields();

        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        if ($isHasData) {
            parent::fill($customer);
        }
        if (null !== $address) {
            $this->openTab('addresses');
            $this->getTabElement('addresses')->fillAddresses($address);
        }

        return $this;
    }

    /**
     * Update Customer forms on tabs by customer, addresses data.
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function updateCustomer(FixtureInterface $customer, $address = null)
    {
        $this->waitForm();

        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        if ($isHasData) {
            parent::fill($customer);
        }
        if (null !== $address) {
            $this->openTab('addresses');
            $this->getTabElement('addresses')->updateAddresses($address);
        }

        return $this;
    }

    /**
     * Get data of Customer information, addresses on tabs.
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return array
     */
    public function getDataCustomer(FixtureInterface $customer, $address = null)
    {
        $this->waitForm();

        $data = ['customer' => $customer->hasData() ? parent::getData($customer) : parent::getData()];
        if (null !== $address) {
            $this->openTab('addresses');
            $data['addresses'] = $this->getTabElement('addresses')->getDataAddresses($address);
        }

        return $data;
    }

    /**
     * Wait for User before fill form which calls JS validation on correspondent form.
     * See details in MAGETWO-31435.
     *
     * @return void
     */
    protected function waitForm()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible($this->activeFormTab);
        sleep(20);
    }

    /**
     * Wait for User before fill form which calls JS validation on correspondent fields of form.
     * See details in MAGETWO-31435.
     *
     * @return void
     */
    protected function waitFields()
    {
        /* Wait for field label is visible in the form */
        $this->waitForElementVisible($this->fieldLabel, Locator::SELECTOR_XPATH);
        /* Wait for render all field's labels(assert that absent field without label) in the form */
        $this->waitForElementNotVisible($this->fieldLabelAbsent, Locator::SELECTOR_XPATH);
        /* Wait for field's control block is visible in the form */
        $this->waitForElementVisible($this->fieldWrapperControl, Locator::SELECTOR_XPATH);
        /* Wait for render all field's control blocks(assert that absent field without control block) in the form */
        $this->waitForElementNotVisible($this->fieldWrapperControlAbsent, Locator::SELECTOR_XPATH);
    }
}
