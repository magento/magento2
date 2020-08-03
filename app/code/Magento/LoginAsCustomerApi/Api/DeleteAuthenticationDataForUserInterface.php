<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Delete authentication data by user id
 *
 * @api
 */
interface DeleteAuthenticationDataForUserInterface
{
    /**
     * Delete authentication data by user id
     *
     * @param int $userId
     * @return void
     */
    public function execute(int $userId): void;
}
