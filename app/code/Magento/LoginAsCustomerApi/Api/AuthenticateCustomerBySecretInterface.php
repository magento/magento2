<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Authenticate a customer by secret
 *
 * @api
 */
interface AuthenticateCustomerBySecretInterface
{
    /**
     * Authenticate a customer by secret
     *
     * @param string $secret
     * @return void
     * @throws LocalizedException
     */
    public function execute(string $secret): void;
}
