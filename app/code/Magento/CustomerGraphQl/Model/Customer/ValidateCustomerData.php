<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class ValidateCustomerData
{
    /**
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
     * @param array $customerData
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(array $customerData): void
    {
        $attributes = $this->getAllowedCustomerAttributes->execute(array_keys($customerData));
        $errorInput = [];

        foreach ($attributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired() && empty($customerData[$attributeName])) {
                $errorInput[] = $attributeName;
            }
        }

        if ($errorInput) {
            throw new GraphQlInputException(
                __('Required parameters are missing: %1', [implode(', ', $errorInput)])
            );
        }
    }
}