<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

/**
 * Customer data validation used during customer account creation and updating
 */
class ValidateCustomerData
{
    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * Get allowed/required customer attributes
     *
     * @var GetAllowedCustomerAttributes
     */
    private $getAllowedCustomerAttributes;

    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    /**
     * ValidateCustomerData constructor.
     *
     * @param GetAllowedCustomerAttributes $getAllowedCustomerAttributes
     * @param EmailAddressValidator $emailAddressValidator
     * @param CustomerMetadataInterface $customerMetadata
     */
    public function __construct(
        GetAllowedCustomerAttributes $getAllowedCustomerAttributes,
        EmailAddressValidator $emailAddressValidator,
        CustomerMetadataInterface $customerMetadata
    ) {
        $this->getAllowedCustomerAttributes = $getAllowedCustomerAttributes;
        $this->emailAddressValidator = $emailAddressValidator;
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Validate customer data
     *
     * @param array $customerData
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(array $customerData): void
    {
        $this->validateRequiredArguments($customerData);
        $this->validateEmail($customerData);
        $this->validateGender($customerData);
    }

    /**
     * Validate required attributes
     *
     * @param array $customerData
     * @throws GraphQlInputException
     */
    private function validateRequiredArguments(array $customerData): void
    {
        $attributes = $this->getAllowedCustomerAttributes->execute(array_keys($customerData));
        $errorInput = [];

        foreach ($attributes as $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (!isset($customerData[$attributeInfo->getAttributeCode()])
                    || $customerData[$attributeInfo->getAttributeCode()] == '')
            ) {
                $errorInput[] = $attributeInfo->getDefaultFrontendLabel();
            }
        }

        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameters are missing: %1', [implode(', ', $errorInput)])
            );
        }
    }

    /**
     * Validate an email
     *
     * @param array $customerData
     * @throws GraphQlInputException
     */
    private function validateEmail(array $customerData): void
    {
        if (isset($customerData['email']) && !$this->emailAddressValidator->isValid($customerData['email'])) {
            throw new GraphQlInputException(
                __('"%1" is not a valid email address.', $customerData['email'])
            );
        }
    }

    /**
     * Validate gender value
     *
     * @param array $customerData
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function validateGender(array $customerData): void
    {
        if (isset($customerData['gender']) && $customerData['gender']) {
            /** @var AttributeMetadata $genderData */
            $options = $this->customerMetadata->getAttributeMetadata('gender')->getOptions();

            $isValid = false;
            foreach ($options as $optionData) {
                if ($optionData->getValue() && $optionData->getValue() == $customerData['gender']) {
                    $isValid = true;
                }
            }

            if (!$isValid) {
                throw new GraphQlInputException(
                    __('"%1" is not a valid gender value.', $customerData['gender'])
                );
            }
        }
    }
}
