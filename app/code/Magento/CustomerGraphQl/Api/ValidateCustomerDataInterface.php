<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
