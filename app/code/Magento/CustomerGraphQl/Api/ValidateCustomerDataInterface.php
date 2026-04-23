<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Api;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Interface for customer data validator
 */
interface ValidateCustomerDataInterface
{
    /**
     * Validate customer data
     *
     * @param array $customerData
     * @throws GraphQlInputException
     */
    public function execute(array $customerData): void;
}
