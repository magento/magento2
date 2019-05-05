<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

/**
 * Interface for managing customers accounts.
 * @api
 * @since 100.0.2
 */
interface AccountAuthManagementInterface
{
    /**
     * Authenticate a customer by username and password
     *
     * @param string $email
     * @param string $password
     * @param int|null $websiteId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($email, $password, $websiteId = null);
}
