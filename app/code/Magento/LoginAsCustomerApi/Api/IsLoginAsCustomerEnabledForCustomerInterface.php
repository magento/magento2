<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\LoginAsCustomerApi\Api\Data\IsLoginAsCustomerEnabledForCustomerResultInterface;

/**
 * Check if Login as Customer functionality is enabled for Customer.
 */
interface IsLoginAsCustomerEnabledForCustomerInterface
{
    /**
     * Check if Login as Customer functionality is enabled for Customer.
     *
     * @param int $customerId
     * @return IsLoginAsCustomerEnabledForCustomerResultInterface
     */
    public function execute(int $customerId): IsLoginAsCustomerEnabledForCustomerResultInterface;
}
