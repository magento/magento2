<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

/**
 * Customer data validation used during customer account creation and updating
 */
class ValidateCustomerData
{
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
     */
    public function __construct(
        GetAllowedCustomerAttributes $getAllowedCustomerAttributes,
        EmailAddressValidator $emailAddressValidator
    ) {
        $this->getAllowedCustomerAttributes = $getAllowedCustomerAttributes;
        $this->emailAddressValidator = $emailAddressValidator;
    }

    /**
     * Validate customer data
     *
     * @param array $customerData
     *
     * @return void
     *
     * @throws GraphQlInputException
     */
    public function execute(array $customerData): void
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

        if (isset($customerData['email']) && !$this->emailAddressValidator->isValid($customerData['email'])) {
            throw new GraphQlInputException(
                __('"%1" is not a valid email address.', $customerData['email'])
            );
        }
    }
}
