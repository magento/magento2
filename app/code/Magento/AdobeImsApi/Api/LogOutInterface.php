<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeImsApi\Api;

/**
 * Declare functionality for user logout from the Adobe account
 *
 * @api
 */
interface LogOutInterface
{
    /**
     * LogOut User from Adobe Account
     *
     * @return bool
     */
    public function execute() : bool;
}
