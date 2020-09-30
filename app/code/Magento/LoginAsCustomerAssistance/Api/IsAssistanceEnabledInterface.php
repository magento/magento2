<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Api;

/**
 * Get 'assistance_allowed' attribute from Customer.
 */
interface IsAssistanceEnabledInterface
{
    /**
     * Merchant assistance denied by customer status code.
     */
    public const DENIED = 1;

    /**
     * Merchant assistance allowed by customer status code.
     */
    public const ALLOWED = 2;

    /**
     * Get 'assistance_allowed' attribute from Customer by id.
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool;
}
