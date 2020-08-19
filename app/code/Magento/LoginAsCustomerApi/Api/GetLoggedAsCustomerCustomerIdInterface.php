<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Get id of Customer Admin is logged as.
 */
interface GetLoggedAsCustomerCustomerIdInterface
{
    /**
     * Get id of Customer Admin is logged as.
     *
     * @return int
     */
    public function execute(): int;
}
