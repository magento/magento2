<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Address\Validator;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Service for validating quote address attributes using customer address validation rules
 */
class AddressAttributeValidator implements AddressAttributeValidatorInterface
{
    /**
     * @param ValidatorFactory $validatorFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        private ValidatorFactory $validatorFactory,
        private AddressFactory $addressFactory
    ) {
    }

    /**
     * Validate address attributes using customer_address validator with custom attributes
     *
     * @param AddressInterface $address
     * @param string $addressType
     * @return void
     * @throws InputException
     */
    public function validate(AddressInterface $address, string $addressType): void
    {
        try {
            $customerAddress = $this->createCustomerAddressFromQuoteAddress($address);

            $validator = $this->validatorFactory->createValidator('customer_address', 'save');
            if (!$validator->isValid($customerAddress)) {
                $this->throwValidationException($validator->getMessages(), $addressType);
            }
        } catch (LocalizedException $e) {
            throw new InputException(__($e->getMessage()));
        }
    }

    /**
     * Create customer address object from quote address
     *
     * @param AddressInterface $address
     * @return Address
     */
    private function createCustomerAddressFromQuoteAddress(
        AddressInterface $address
    ): Address {
        $customerAddress = $this->addressFactory->create();
        $customerAddress->setPrefix($address?->getPrefix());
        $customerAddress->setFirstname($address->getFirstname());
        $customerAddress->setMiddlename($address?->getMiddlename());
        $customerAddress->setLastname($address->getLastname());
        $customerAddress->setSuffix($address?->getSuffix());
        $customerAddress->setCompany($address?->getCompany());
        $customerAddress->setStreet($address->getStreet());
        $customerAddress->setCountryId($address->getCountryId());
        $customerAddress->setCity($address->getCity());
        $customerAddress->setPostcode($address->getPostcode());
        $customerAddress->setTelephone($address->getTelephone());
        $customerAddress->setFax($address?->getFax());
        $customerAddress->setVatId($address?->getVatId());
        $regionData = $address->getRegion();
        if (is_array($regionData)) {
            $customerAddress->setRegion($regionData['region'] ?? null);
            $customerAddress->setRegionCode($regionData['region_code'] ?? null);
            $customerAddress->setRegionId($regionData['region_id'] ?? null);
        } elseif (is_string($regionData)) {
            $customerAddress->setRegion($regionData);
        } else {
            $customerAddress->setRegion(null);
        }

        if ($address instanceof AbstractExtensibleModel) {
            $customAttributesData = $address->getData();

            foreach ($customAttributesData as $attributeCode => $value) {
                if (!$customerAddress->getData($attributeCode)) {
                    $customerAddress->setData($attributeCode, $value);
                }
            }
        }

        return $customerAddress;
    }

    /**
     * Process validator messages and throw validation exception
     *
     * @param array $messages
     * @param string $addressType
     * @return void
     * @throws ValidationException
     */
    private function throwValidationException(array $messages, string $addressType): void
    {
        $errorMessages = [];
        foreach ($messages as $message) {
            if (is_array($message)) {
                foreach ($message as $msg) {
                    $errorMessages[] = $msg;
                }
            } else {
                $errorMessages[] = $message;
            }
        }
        throw new ValidationException(
            __(
                'The %1 address contains invalid data: %2',
                $addressType,
                implode(', ', $errorMessages)
            )
        );
    }
}
