<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Api;

/**
 * @api
 */
interface CreateSecretInterface
{
    /**
     * Create a new secret key
     * @return string
     * @param int $customerId
     * @param int $adminId
     */
    public function execute(int $customerId, int $adminId):string;
}
