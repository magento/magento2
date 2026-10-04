<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Get id of Customer Admin is logged as.
 *
 * @api
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
