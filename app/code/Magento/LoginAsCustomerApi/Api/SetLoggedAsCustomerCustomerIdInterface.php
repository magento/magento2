<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Set id of Customer Admin is logged as.
 *
 * @api
 */
interface SetLoggedAsCustomerCustomerIdInterface
{
    /**
     * Set id of Customer Admin is logged as.
     *
     * @param int $customerId
     * @return void
     */
    public function execute(int $customerId): void;
}
