<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Get id of Admin logged as Customer.
 *
 * @api
 */
interface GetLoggedAsCustomerAdminIdInterface
{
    /**
     * Get id of Admin logged as Customer.
     *
     * @return int
     */
    public function execute(): int;
}
