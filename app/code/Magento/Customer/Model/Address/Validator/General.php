<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Validator;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;

/**
 * Address general fields validator.
 */
class General implements ValidatorInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryData;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Directory\Helper\Data $directoryData
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Directory\Helper\Data $directoryData
    ) {
        $this->eavConfig = $eavConfig;
        $this->directoryData = $directoryData;
    }

    /**
     * @inheritdoc
     */
    public function validate(AbstractAddress $address)
    {
        $errors = array_merge(
            $this->checkRequiredFields($address),
            $this->checkOptionalFields($address)
        );

        return $errors;
    }

    /**
     * Check fields that are generally required.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws ValidateException
     */
    private function checkRequiredFields(AbstractAddress $address)
    {
        $errors = [];
        if (!ValidatorChain::is($address->getFirstname(), NotEmpty::class)) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'firstname']);
        }

        if (!ValidatorChain::is($address->getLastname(), NotEmpty::class)) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'lastname']);
        }

        if (!ValidatorChain::is($address->getStreetLine(1), NotEmpty::class)) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'street']);
        }

        if (!ValidatorChain::is($address->getCity(), NotEmpty::class)) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'city']);
        }

        return $errors;
    }

    /**
     * Check fields that are conditionally required.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws LocalizedException|ValidateException
     */
    private function checkOptionalFields(AbstractAddress $address)
    {
        $this->reloadAddressAttributes($address);
        $errors = [];
        if ($this->isTelephoneRequired()
            && !ValidatorChain::is($address->getTelephone(), NotEmpty::class)
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'telephone']);
        }

        if ($this->isFaxRequired()
            && !ValidatorChain::is($address->getFax(), NotEmpty::class)
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'fax']);
        }

        if ($this->isCompanyRequired()
            && !ValidatorChain::is($address->getCompany(), NotEmpty::class)
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'company']);
        }

        $havingOptionalZip = $this->directoryData->getCountriesWithOptionalZip();
        if (!in_array($address->getCountryId(), $havingOptionalZip)
            && !ValidatorChain::is($address->getPostcode(), NotEmpty::class)
        ) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'postcode']);
        }

        return $errors;
    }

    /**
     * Check if company field required in configuration.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isCompanyRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'company')->getIsRequired();
    }

    /**
     * Check if telephone field required in configuration.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isTelephoneRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'telephone')->getIsRequired();
    }

    /**
     * Check if fax field required in configuration.
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isFaxRequired()
    {
        return $this->eavConfig->getAttribute('customer_address', 'fax')->getIsRequired();
    }

    /**
     * Reload address attributes for the certain store
     *
     * @param AbstractAddress $address
     * @return void
     */
    private function reloadAddressAttributes(AbstractAddress $address): void
    {
        $attributeSetId = $address->getAttributeSetId() ?: AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS;
        $address->setData('attribute_set_id', $attributeSetId);
        $this->eavConfig->getEntityAttributes(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $address);
    }
}
