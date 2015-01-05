<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Integration\Service\V1;

use Magento\Framework\Exception\AuthenticationException;

/**
 * Interface providing token generation for Customers
 */
interface CustomerTokenServiceInterface
{
    /**
     * Create access token for admin given the customer credentials.
     *
     * @param string $username
     * @param string $password
     * @return string Token created
     * @throws AuthenticationException
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
