<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CustomerInjectable
 * Customer repository
 */
class CustomerInjectable extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['johndoe'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
            'group_id' => ['dataSet' => 'General'],
        ];

        $this->_data['johndoe_retailer'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'Retailer'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
        ];

        $this->_data['johndoe_with_balance'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'dob' => '01/01/1990',
            'gender' => 'Male',
            'amount_delta' => 501,
        ];

        $this->_data['defaultBackend'] = [
            'website_id' => 'Main Website',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
        ];

        $this->_data['johndoe_with_addresses'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'address' => ['presets' => 'US_address'],
        ];

        $this->_data['customer_US'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['customer_UK'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'JaneDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
        ];

        $this->_data['johndoe_unique'] = [
            'firstname' => 'John',
            'lastname' => 'Doe%isolation%',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'address' => ['presets' => 'US_address_NY'],
        ];

        $this->_data['johndoe_unique_TX'] = [
            'firstname' => 'John',
            'lastname' => 'Doe%isolation%',
            'group_id' => ['dataSet' => 'General'],
            'email' => 'JohnDoe_%isolation%@example.com',
            'password' => '123123q',
            'password_confirmation' => '123123q',
            'address' => ['presets' => 'US_address_TX'],
        ];
    }
}
