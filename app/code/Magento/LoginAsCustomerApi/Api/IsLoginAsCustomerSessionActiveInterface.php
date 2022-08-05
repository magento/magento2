<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Check if Login as Customer session is still active.
 *
 * @api
 * @since 100.4.0
 */
interface IsLoginAsCustomerSessionActiveInterface
{
    /**
     * Check if Login as Customer session is still active.
     *
     * @param int $customerId
     * @param int $userId
     * @return bool
     * @since 100.4.0
     */
    public function execute(int $customerId, int $userId): bool;
}
