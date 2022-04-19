<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api;

/**
 * Declare functionality for user logout from the Adobe IMS account
 *
 * @api
 */
interface ImsLogOutInterface
{
    /**
     * LogOut User from Adobe IMS Account
     *
     * @param string|null $accessToken
     * @return bool
     */
    public function execute(?string $accessToken = null) : bool;
}
