<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

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
     * ValidateCustomerData constructor.
     *
     * @param GetAllowedCustomerAttributes $getAllowedCustomerAttributes
     */
    public function __construct(GetAllowedCustomerAttributes $getAllowedCustomerAttributes)
    {
        $this->getAllowedCustomerAttributes = $getAllowedCustomerAttributes;
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
    }
}
