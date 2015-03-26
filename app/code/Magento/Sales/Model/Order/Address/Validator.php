<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Address;

use Magento\Sales\Model\Order\Address;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\CountryFactory;
/**
 * Class Validator
 */
class Validator
{
    /**
     * @var array
     */
    protected $required = [
        'parent_id' => 'Parent Order Id',
        'postcode' => 'Zip code',
        'lastname' => 'Last name',
        'street' => 'Street',
        'city' => 'City',
        'email' => 'Email',
        'telephone' => 'Phone Number',
        'country_id' => 'Country',
        'firstname' => 'First Name',
        'address_type' => 'Address Type',
    ];

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;
    /**
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        CountryFactory $countryFactory
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->countryFactory = $countryFactory;
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];
        foreach ($this->required as $code => $label) {
            if (!$address->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }
        if (!filter_var($address->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $warnings[] = 'Email has a wrong format';
        }
        if (!filter_var(in_array($address->getAddressType(), [Address::TYPE_BILLING, Address::TYPE_SHIPPING]))) {
            $warnings[] = 'Address type doesn\'t match required options';
        }
        return $warnings;
    }

    /**
     * Validate address attribute for payment operations
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param Address $address
     */
    public function validateForPayment(Address $address)
    {
        $country = $this->countryFactory->create()->load($address->getCountryId());
        $errors = [];
        if (!\Zend_Validate::is($address->getFirstname(), 'NotEmpty')) {
            $errors[] = __('Please enter the first name.');
        }
        if (!\Zend_Validate::is($address->getLastname(), 'NotEmpty')) {
            $errors[] = __('Please enter the last name.');
        }
        if (!\Zend_Validate::is($address->getStreetLine(1), 'NotEmpty')) {
            $errors[] = __('Please enter the street.');
        }
        if (!\Zend_Validate::is($address->getCity(), 'NotEmpty')) {
            $errors[] = __('Please enter the city.');
        }
        if (!\Zend_Validate::is($address->getTelephone(), 'NotEmpty')) {
            $errors[] = __('Please enter the phone number.');
        }
        $havingOptionalZip = $this->directoryHelper->getCountriesWithOptionalZip();
        if (!in_array(
                $address->getCountryId(),
                $havingOptionalZip
            ) && !\Zend_Validate::is(
                $address->getPostcode(),
                'NotEmpty'
            )
        ) {
            $errors[] = __('Please enter the zip/postal code.');
        }
        if (!\Zend_Validate::is($address->getCountryId(), 'NotEmpty')) {
            $errors[] = __('Please enter the country.');
        }
        if ($country->getRegionCollection()->getSize() && !\Zend_Validate::is(
                $address->getRegionId(),
                'NotEmpty'
            ) && $this->directoryHelper->isRegionRequired(
                $address->getCountryId()
            )
        ) {
            $errors[] = __('Please enter the state/province.');
        }
        if (empty($errors) || $address->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
}
