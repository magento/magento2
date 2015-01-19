<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class AddressInjectable
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AddressInjectable extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Customer\Test\Repository\AddressInjectable';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Customer\Test\Handler\AddressInjectable\AddressInjectableInterface';

    protected $defaultDataSet = [
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
    ];

    protected $city = [
        'attribute_code' => 'city',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $default_billing = [
        'attribute_code' => 'default_billing',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'checkbox',
    ];

    protected $default_shipping = [
        'attribute_code' => 'default_shipping',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'checkbox',
    ];

    protected $company = [
        'attribute_code' => 'company',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $country_id = [
        'attribute_code' => 'country_id',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $fax = [
        'attribute_code' => 'fax',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $firstname = [
        'attribute_code' => 'firstname',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $lastname = [
        'attribute_code' => 'lastname',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $email = [
        'attribute_code' => 'email',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $middlename = [
        'attribute_code' => 'middlename',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $postcode = [
        'attribute_code' => 'postcode',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $prefix = [
        'attribute_code' => 'prefix',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $region = [
        'attribute_code' => 'region',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $region_id = [
        'attribute_code' => 'region_id',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'hidden',
    ];

    protected $street = [
        'attribute_code' => 'street',
        'backend_type' => 'text',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'multiline',
    ];

    protected $suffix = [
        'attribute_code' => 'suffix',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $telephone = [
        'attribute_code' => 'telephone',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $vat_id = [
        'attribute_code' => 'vat_id',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $vat_is_valid = [
        'attribute_code' => 'vat_is_valid',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $vat_request_date = [
        'attribute_code' => 'vat_request_date',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $vat_request_id = [
        'attribute_code' => 'vat_request_id',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $vat_request_success = [
        'attribute_code' => 'vat_request_success',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    public function getCity()
    {
        return $this->getData('city');
    }

    public function getDefaultShipping()
    {
        return $this->getData('default_shipping');
    }

    public function getDefaultBilling()
    {
        return $this->getData('default_billing');
    }

    public function getEmail()
    {
        return $this->getData('email');
    }

    public function getCompany()
    {
        return $this->getData('company');
    }

    public function getCountryId()
    {
        return $this->getData('country_id');
    }

    public function getFax()
    {
        return $this->getData('fax');
    }

    public function getFirstname()
    {
        return $this->getData('firstname');
    }

    public function getLastname()
    {
        return $this->getData('lastname');
    }

    public function getMiddlename()
    {
        return $this->getData('middlename');
    }

    public function getPostcode()
    {
        return $this->getData('postcode');
    }

    public function getPrefix()
    {
        return $this->getData('prefix');
    }

    public function getRegion()
    {
        return $this->getData('region');
    }

    public function getRegionId()
    {
        return $this->getData('region_id');
    }

    public function getStreet()
    {
        return $this->getData('street');
    }

    public function getSuffix()
    {
        return $this->getData('suffix');
    }

    public function getTelephone()
    {
        return $this->getData('telephone');
    }

    public function getVatId()
    {
        return $this->getData('vat_id');
    }

    public function getVatIsValid()
    {
        return $this->getData('vat_is_valid');
    }

    public function getVatRequestDate()
    {
        return $this->getData('vat_request_date');
    }

    public function getVatRequestId()
    {
        return $this->getData('vat_request_id');
    }

    public function getVatRequestSuccess()
    {
        return $this->getData('vat_request_success');
    }
}
