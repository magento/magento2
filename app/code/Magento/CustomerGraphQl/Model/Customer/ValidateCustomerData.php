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
 * Class ValidateCustomerData
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
        if (!$this->emailIsValid($customerData['email'])) {
            throw new GraphQlInputException(
                __('"%1" is not a valid email address.', $customerData['email'])
            );
        }

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
     * Validate if email address is valid
     *
     * In order to work the same as in admin panel, the patter for validation was selected from 'validate-email'
     * function in app/code/Magento/Ui/view/base/web/js/lib/validation/rules.js
     *
     * @param string $email
     *
     * @return bool
     */
    private function emailIsValid(string $email): bool
    {
        $regex = "/^([a-z0-9,!\#$%&'\*\+\/=\?\^_`\{\|\}~-]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])+(\.(["
            . "a-z0-9,!\#$%&'\*\+\/=\?\^_`\{\|\}~-]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])+)*@([a-z0-9-"
            . "]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}])+(\.([a-z0-9-]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDC"
            . "F}\x{FDF0}-\x{FFEF}])+)*\.(([a-z]|[\x{00A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}]){2,})$/iu";

        return !empty($email)
            && preg_match($regex, $email)
            && $this->emailAddressValidator->isValid($email);
    }
}
