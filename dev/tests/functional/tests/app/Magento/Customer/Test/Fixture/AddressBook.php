<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

/**
 * Address of registered customer
 */
class AddressBook extends \Mtf\Fixture\DataFixture
{
    /**
     * @var \Mtf\Fixture\DataFixture
     */
    protected $_addressFixture;

    /**
     * Nothing to initialize
     */
    protected function _initData()
    {
    }

    /**
     * Set address fixture
     *
     * @param \Mtf\Fixture\DataFixture $address
     */
    public function setAddress(\Mtf\Fixture\DataFixture $address)
    {
        $this->_addressFixture = $address;
    }

    /**
     * Switch current data set
     *
     * @param $name
     * @return bool
     */
    public function switchData($name)
    {
        $result = $this->_addressFixture->switchData($name);
        if (!$result) {
            return false;
        }
        $data = $this->_addressFixture->getData();
        $this->_data = ['fields' => ['address_id' => [
            'value' => $data['fields']['firstname']['value'] . ' '
                . $data['fields']['lastname']['value'] . ', '
                . $data['fields']['street']['value'] . ', '
                . $data['fields']['city']['value'] . ', '
                . $data['fields']['region_id']['value'] . ' '
                . $data['fields']['postcode']['value'] . ', '
                . $data['fields']['country_id']['value'],
            'input' => 'select',
        ]]];

        return $result;
    }
}
