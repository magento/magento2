<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Api;

/**
 * Interface providing token generation for Customers
 *
 * @api
 */
interface CustomerTokenServiceInterface
{
    /**
     * Create access token for admin given the customer credentials.
     *
     * @param string $username
     * @param string $password
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function createCustomerAccessToken($username, $password);

    /**
     * Revoke token by customer id.
     *
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeCustomerAccessToken($customerId);
}
