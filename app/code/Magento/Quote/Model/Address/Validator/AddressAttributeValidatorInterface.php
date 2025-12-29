<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Address\Validator;

use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface for validating quote address attributes
 */
interface AddressAttributeValidatorInterface
{
    /**
     * Validate address attributes using customer_address validator with custom attributes
     *
     * @param AddressInterface $address
     * @param string $addressType
     * @return void
     * @throws InputException
     */
    public function validate(AddressInterface $address, string $addressType): void;
}
