<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Account Management service implementation for external API access.
 * Handle various customer account actions.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagementApi extends AccountManagement
{
    /**
     * @inheritDoc
     *
     * Override createAccount method to unset confirmation attribute for security purposes.
     */
    public function createAccount(CustomerInterface $customer, $password = null, $redirectUrl = '')
    {
        $customer = parent::createAccount($customer, $password, $redirectUrl);
        $customer->setConfirmation(null);

        return $customer;
    }
}
