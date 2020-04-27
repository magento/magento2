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
interface GetAuthenticateDataInterface
{
    /**
     * Load login details based on secret key
     */
    public function execute(string $secretKey):array;
}
