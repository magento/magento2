<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Customer\Test\Fixture\Address;
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
    protected $spinner = '#container [data-role="spinner"]';

    /**
     * Customer form to load.
     *
     * @var string
     */
    protected $activeFormTab = '#container [data-area-active="true"]';

    /**
     * Field wrapper with label on form.
     *
     * @var string
     */
    protected $fieldLabel = './/*[contains(@class, "admin__field")]/*[contains(@class,"label")]';

    /**
     * Field wrapper with control block on form.
     *
     * @var string
     */
    protected $fieldWrapperControl = './/*[contains(@class, "admin__field")]/*[contains(@class,"control")]';

    /**
     * Selector for waiting tab content to load.
     *
     * @var string
     */
    protected $tabReadiness = '.admin__page-nav-item._active._loading';

    /**
     * Personal information xpath selector.
     *
     * @var string
     */
    protected $information = './/th[contains(text(), "%s")]/following-sibling::td[1]';

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

        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        if ($isHasData) {
            parent::fill($customer);
        }
        if (null !== $address) {
            $this->openTab('addresses');
            $this->getTab('addresses')->fillAddresses($address);
        }

        return $this;
    }

    /**
     * Update Customer forms on tabs by customer, addresses data.
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @param Address|null $addressToDelete
     * @return $this
     */
    public function updateCustomer(FixtureInterface $customer, $address = null, Address $addressToDelete = null)
    {
        $this->waitForm();

        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        if ($isHasData) {
            parent::fill($customer);
        }
        if ($addressToDelete !== null) {
            $this->openTab('addresses');
            $this->getTab('addresses')->deleteCustomerAddress($addressToDelete);
        }
        if ($address !== null) {
            $this->openTab('addresses');
            $this->getTab('addresses')->updateAddresses($address);
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
            $data['addresses'] = $this->getTab('addresses')->getDataAddresses($address);
        }

        return $data;
    }

    /**
     * Wait for User before fill form which calls JS validation on correspondent form.
     *
     * @return void
     */
    protected function waitForm()
    {
        $this->waitForElementNotVisible($this->spinner);
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return CustomerForm
     */
    public function openTab($tabName)
    {
        parent::openTab($tabName);
        $this->waitForElementNotVisible($this->tabReadiness);

        return $this;
    }

    /**
     * Get array of label => js error text.
     *
     * @return array
     */
    public function getJsErrors()
    {
        $tabs = ['account_information', 'addresses'];
        $jsErrors = [];
        foreach ($tabs as $tabName) {
            $tab = $this->getTab($tabName);
            $this->openTab($tabName);
            $jsErrors = array_merge($jsErrors, $tab->getJsErrors());
        }
        return $jsErrors;
    }

    /**
     * Get personal information.
     *
     * @param string $title
     * @return string
     */
    public function getPersonalInformation($title)
    {
        return $this->_rootElement
            ->find(sprintf($this->information, $title), Locator::SELECTOR_XPATH)
            ->getText();
    }
}
