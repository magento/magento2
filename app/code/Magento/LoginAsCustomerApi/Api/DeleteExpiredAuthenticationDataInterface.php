<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Delete expired authentication data
 *
 * @api
 */
interface DeleteExpiredAuthenticationDataInterface
{
    /**
     * Delete expired authentication data
     *
     * @return void
     */
    public function execute(): void;
}
