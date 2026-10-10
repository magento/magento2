<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

/**
 * Delete authentication data by secret
 *
 * @api
 */
interface DeleteAuthenticationDataForCustomerInterface
{
    /**
     * Delete authentication data by secret
     *
     * @param string $secret
     * @return void
     */
    public function execute(string $secret): void;
}
