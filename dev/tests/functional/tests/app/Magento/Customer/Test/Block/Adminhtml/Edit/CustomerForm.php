<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class CustomerForm
 * Form for creation of the customer
 */
class CustomerForm extends FormTabs
{
    /**
     * Fill Customer forms on tabs by customer, addresses data
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function fillCustomer(FixtureInterface $customer, $address = null)
    {
        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        $this->waitBeforeFill();
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
     * Update Customer forms on tabs by customer, addresses data
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function updateCustomer(FixtureInterface $customer, $address = null)
    {
        $isHasData = ($customer instanceof InjectableFixture) ? $customer->hasData() : true;
        $this->waitBeforeFill();
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
    protected function waitBeforeFill()
    {
        usleep(500000);
    }
}
