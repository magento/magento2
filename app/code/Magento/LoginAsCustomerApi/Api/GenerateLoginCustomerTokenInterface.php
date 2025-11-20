<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;


/**
 * Interface defining the contract for generating access tokens used by the LoginAsCustomer functionality.
 *
 * @api
 */
interface GenerateLoginCustomerTokenInterface
{
    /**
     * Create access token with secret given the customer credentials.
     *
     * @param string $secret Temporary secret issued for Login As Customer authorization.
     * @return string Generated access token
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function createCustomerAccessToken(string $secret): string;
}
