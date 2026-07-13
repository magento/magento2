<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Set id of Admin logged as Customer.
 *
 * @api
 */
interface SetLoggedAsCustomerAdminIdInterface
{
    /**
     * Set id of Admin logged as Customer.
     *
     * @param int $adminId
     * @return void
     */
    public function execute(int $adminId): void;
}
