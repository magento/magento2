<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Delete authentication data by secret
 *
 * @api
 */
interface DeleteAuthenticationDataBySecretInterface
{
    /**
     * Delete authentication data by secret
     *
     * @param string $secret
     * @return void
     */
    public function execute(string $secret): void;
}
