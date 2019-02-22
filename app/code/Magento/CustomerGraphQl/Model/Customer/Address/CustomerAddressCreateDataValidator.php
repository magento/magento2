<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Customer address create data validator
 */
class CustomerAddressCreateDataValidator
{
    /**
     * @var GetAllowedAddressAttributes
     */
    private $getAllowedAddressAttributes;

    /**
     * @param GetAllowedAddressAttributes $getAllowedAddressAttributes
     */
    public function __construct(GetAllowedAddressAttributes $getAllowedAddressAttributes)
    {
        $this->getAllowedAddressAttributes = $getAllowedAddressAttributes;
    }

    /**
     * Validate customer address create data
     *
     * @param array $addressData
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(array $addressData): void
    {
        $attributes = $this->getAllowedAddressAttributes->execute();
        $errorInput = [];

        foreach ($attributes as $attributeName => $attributeInfo) {
            if ($attributeInfo->getIsRequired()
                && (!isset($addressData[$attributeName]) || empty($addressData[$attributeName]))
            ) {
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
