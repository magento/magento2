<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class AddressInjectable
 * Customer address repository
 */
class AddressInjectable extends AbstractRepository
{
    /**
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['US_address'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '6161 West Centinela Avenue',
            'city' => 'Culver City',
            'region_id' => 'California',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
            'default_billing' => 'Yes',
            'default_shipping' => 'Yes',
        ];

        $this->_data['US_address_default_billing'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '6161 West Centinela Avenue',
            'city' => 'Culver City',
            'region_id' => 'California',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
            'default_billing' => 'Yes',
            'default_shipping' => 'No',
        ];

        $this->_data['US_NY_address_billing'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '6262 Fifth Avenue',
            'city' => 'New York',
            'region_id' => 'New York',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
            'default_billing' => 'No',
            'default_shipping' => 'No',
        ];

        $this->_data['US_address_default_shipping'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '6161 West Centinela Avenue',
            'city' => 'Culver City',
            'region_id' => 'California',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
            'default_billing' => 'Yes',
            'default_shipping' => 'No',
        ];

        $this->_data['default_US_address'] = [
            'company' => 'Magento %isolation%',
            'street' => '6161 West Centinela Avenue',
            'city' => 'Culver City',
            'region_id' => 'California',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
            'default_billing' => 'Yes',
            'default_shipping' => 'Yes',
        ];

        $this->_data['US_address_without_email'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'street' => '6161 West Centinela Avenue',
            'city' => 'Culver City',
            'region_id' => 'California',
            'postcode' => '90230',
            'country_id' => 'United States',
            'telephone' => '555-55-555-55',
        ];

        $this->_data['US_address_NY'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '3222 Cliffside Drive',
            'city' => 'Binghamton',
            'region_id' => 'New York',
            'postcode' => '13901',
            'country_id' => 'United States',
            'telephone' => '607-481-7802',
            'default_billing' => 'Yes',
            'default_shipping' => 'Yes',
        ];

        $this->_data['US_address_TX'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'John.Doe%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'street' => '7700 W. Parmer Lane Bldg. D',
            'city' => 'Austin',
            'region_id' => 'Texas',
            'postcode' => '78729 ',
            'country_id' => 'United States',
            'telephone' => '512-691-4400',
            'default_billing' => 'Yes',
            'default_shipping' => 'Yes',
        ];

        $this->_data['customer_US'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'JohnDoe_%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'city' => 'Culver City',
            'street' => '6161 West Centinela Avenue',
            'postcode' => '90230',
            'country_id' => 'United States',
            'region_id' => 'California',
            'telephone' => '555-55-555-55',
            'fax' => '555-55-555-55',
        ];

        $this->_data['customer_UK'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'JaneDoe_%isolation%@example.com',
            'company' => 'Magento %isolation%',
            'city' => 'London',
            'street' => '172, Westminster Bridge Rd',
            'postcode' => 'SE1 7RW',
            'country_id' => 'United Kingdom',
            'region' => 'London',
            'telephone' => '444-44-444-44',
            'fax' => '444-44-444-44',
        ];

        $this->_data['address_US_1'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'email' => 'John.Doe%isolation%@example.com',
            'city' => 'Culver City',
            'street' => '6161 West Centinela Avenue',
            'postcode' => '90230',
            'country_id' => 'United States',
            'region_id' => 'California',
            'telephone' => '555-55-555-55',
        ];

        $this->_data['address_US_2'] = [
            'firstname' => 'Billy',
            'lastname' => 'Holiday',
            'company' => 'Magento %isolation%',
            'email' => 'b.holliday@example.net',
            'city' => 'New York',
            'street' => '727 5th Ave',
            'postcode' => '10022',
            'country_id' => 'United States',
            'region_id' => 'New York',
            'telephone' => '777-77-77-77',
        ];

        $this->_data['address_data_US_1'] = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'city' => 'Culver City',
            'street' => '6161 West Centinela Avenue',
            'postcode' => '90230',
            'country_id' => 'United States',
            'region_id' => 'California',
            'telephone' => '555-55-555-55',
        ];

        $this->_data['address_DE'] = [
            'firstname' => 'Jan',
            'lastname' => 'Jansen',
            'company' => 'Magento %isolation%',
            'city' => 'Berlin',
            'street' => 'Augsburger Strabe 41',
            'postcode' => '10789',
            'country_id' => 'Germany',
            'region_id' => 'Berlin',
            'telephone' => '333-33-333-33',
        ];

        $this->_data['address_UK'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'city' => 'London',
            'street' => '172, Westminster Bridge Rd',
            'postcode' => 'SE1 7RW',
            'country_id' => 'United Kingdom',
            'region_id' => 'London',
            'telephone' => '444-44-444-44',
        ];

        $this->_data['address_UK_2'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'city' => 'Manchester',
            'street' => '42 King Street West',
            'postcode' => 'M3 2WY',
            'country_id' => 'United Kingdom',
            'region_id' => 'Manchester',
            'telephone' => '444-44-444-44',
        ];

        $this->_data['address_UK_with_VAT'] = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'company' => 'Magento %isolation%',
            'city' => 'London',
            'street' => '172, Westminster Bridge Rd',
            'postcode' => 'SE1 7RW',
            'country_id' => 'United Kingdom',
            'region_id' => 'London',
            'telephone' => '444-44-444-44',
            'vat_id' => '584451913',
        ];

        $this->_data['address_US_pay_pal'] = [
            'firstname' => 'Dmytro',
            'lastname' => 'Aponasenko',
            'city' => 'Culver City',
            'street' => '1 Main St',
            'postcode' => '90230',
            'country_id' => 'United States',
            'region_id' => 'Culver City',
        ];
    }
}
