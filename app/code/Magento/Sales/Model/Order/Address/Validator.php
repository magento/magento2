<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Address;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use Magento\Sales\Model\Order\Address;

/**
 * Class for validating customer address.
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
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param CountryFactory $countryFactory
     * @param EavConfig|null $eavConfig
     * @param EmailAddressValidator|null $emailAddressValidator
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        CountryFactory $countryFactory,
        ?EavConfig $eavConfig = null,
        ?EmailAddressValidator $emailAddressValidator = null
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->countryFactory = $countryFactory;
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()
            ->get(EavConfig::class);
        $this->emailAddressValidator = $emailAddressValidator ?: ObjectManager::getInstance()
            ->get(EmailAddressValidator::class);
    }

    /**
     * Validate address.
     *
     * @param Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];

        if ($this->isTelephoneRequired()) {
            $this->required['telephone'] = 'Phone Number';
        }

        if ($this->isCompanyRequired()) {
            $this->required['company'] = 'Company';
        }

        if ($this->isFaxRequired()) {
            $this->required['fax'] = 'Fax';
        }

        foreach ($this->required as $code => $label) {
            if (!$address->hasData($code)) {
                $warnings[] = sprintf('"%s" is required. Enter and try again.', $label);
            }
        }

        $email = $address->getEmail();

        if (empty($email) || !$this->emailAddressValidator->isValid($email)) {
            $warnings[] = 'Email has a wrong format';
        }

        if (!in_array($address->getAddressType(), [Address::TYPE_BILLING, Address::TYPE_SHIPPING])) {
            $warnings[] = 'Address type doesn\'t match required options';
        }
        return $warnings;
    }

    /**
     * Validate address attribute for customer creation
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param Address $address
     */
    public function validateForCustomer(Address $address)
    {
        if ($address->getShouldIgnoreValidation()) {
            return true;
        }

        $errors = [];

        if ($this->isEmpty($address->getFirstname())) {
            $errors[] = __('Please enter the first name.');
        }
        if ($this->isEmpty($address->getLastname())) {
            $errors[] = __('Please enter the last name.');
        }
        if ($this->isEmpty($address->getStreetLine(1))) {
            $errors[] = __('Please enter the street.');
        }
        if ($this->isEmpty($address->getCity())) {
            $errors[] = __('Please enter the city.');
        }

        if ($this->isTelephoneRequired()) {
            if ($this->isEmpty($address->getTelephone())) {
                $errors[] = __('Please enter the phone number.');
            }
        }

        if ($this->isCompanyRequired()) {
            if ($this->isEmpty($address->getCompany())) {
                $errors[] = __('Please enter the company.');
            }
        }

        if ($this->isFaxRequired()) {
            if ($this->isEmpty($address->getFax())) {
                $errors[] = __('Please enter the fax number.');
            }
        }

        $countryId = $address->getCountryId();

        if ($this->isZipRequired($countryId) && $this->isEmpty($address->getPostcode())) {
            $errors[] = __('Please enter the zip/postal code.');
        }
        if ($this->isEmpty($countryId)) {
            $errors[] = __('Please enter the country.');
        }
        if ($this->isStateRequired($countryId) && $this->isEmpty($address->getRegionId())) {
            $errors[] = __('Please enter the state/province.');
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Check if value is empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return empty($value);
    }

    /**
     * Checks if zip for current country id is required
     *
     * @param string $countryId
     * @return bool
     */
    protected function isZipRequired($countryId)
    {
        return !in_array($countryId, $this->directoryHelper->getCountriesWithOptionalZip());
    }

    /**
     * Checks if state for current country id is required
     *
     * @param string $countryId
     * @return bool
     */
    protected function isStateRequired($countryId)
    {
        $country = $this->countryFactory->create()->load($countryId);
        return $this->directoryHelper->isRegionRequired($countryId) && $country->getRegionCollection()->getSize();
    }

    /**
     * Check whether telephone is required for address.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function isTelephoneRequired()
    {
        return ($this->eavConfig->getAttribute('customer_address', 'telephone')->getIsRequired());
    }

    /**
     * Check whether company is required for address.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function isCompanyRequired()
    {
        return ($this->eavConfig->getAttribute('customer_address', 'company')->getIsRequired());
    }

    /**
     * Check whether telephone is required for address.
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function isFaxRequired()
    {
        return ($this->eavConfig->getAttribute('customer_address', 'fax')->getIsRequired());
    }
}
