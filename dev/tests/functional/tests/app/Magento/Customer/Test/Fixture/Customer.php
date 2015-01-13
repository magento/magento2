<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class Customer
 *
 */
class Customer extends DataFixture
{
    /**
     * @return \Magento\Customer\Test\Fixture\Address
     */
    public function getSecondShippingAddress()
    {
        $customerAddress = Factory::getFixtureFactory()->getMagentoCustomerAddress();
        $customerAddress->switchData('address_US_2');
        $customerAddress->setCustomer($this);
        return $customerAddress;
    }

    /**
     * Create customer via frontend
     *
     * @return string
     */
    public function persist()
    {
        return Factory::getApp()->magentoCustomerCreateCustomer($this);
    }

    /**
     * Get customer email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData('fields/email/value');
    }

    /**
     * Get customer password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getData('fields/password/value');
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData('fields/firstname/value');
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getData('fields/lastname/value');
    }

    /**
     * Get billing address for customer
     *
     * @return Address
     */
    public function getDefaultBillingAddress()
    {
        $defaultBilling = Factory::getFixtureFactory()->getMagentoCustomerAddress();
        $defaultBilling->switchData($this->getAddressDatasetName());
        $defaultBilling->setCustomer($this);
        return $defaultBilling;
    }

    /**
     * Get default shipping address for customer
     *
     * @return Address
     */
    public function getDefaultShippingAddress()
    {
        $defaultShipping = Factory::getFixtureFactory()->getMagentoCustomerAddress();
        $defaultShipping->switchData($this->getAddressDatasetName());
        $defaultShipping->setCustomer($this);
        return $defaultShipping;
    }

    /**
     * Get customer group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->getData('fields/group_id/value');
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_defaultConfig = [
            'grid_filter' => ['email'],
            'constraint' => 'Success',
        ];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCustomerCustomer($this->_dataConfig, $this->_data);

        //Default data set
        $this->switchData('customer_US_1');
    }

    /**
     * @return \Magento\Customer\Test\Fixture\Address
     */
    public function getAddressData()
    {
        $customerAddress = Factory::getFixtureFactory()->getMagentoCustomerAddress();
        $customerAddress->switchData('address_data_US_1');
        $customerAddress->setCustomer($this);
        return $customerAddress;
    }

    /**
     * Get address dataset name
     *
     * @return string
     */
    protected function getAddressDatasetName()
    {
        return $this->getData('address/dataset/value');
    }

    /**
     *  Update the customer fixture with a new customer group
     *
     * @param $value
     * @param $inputValue
     */
    public function updateCustomerGroup($value, $inputValue)
    {
        $data = [
            'fields' => [
                'group_id' => [
                    'value' => $value,
                    'input_value' => $inputValue,
                ],
            ],
        ];
        $this->_data = array_replace_recursive($this->_data, $data);
    }
}
