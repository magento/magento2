<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model;

/**
 * Interface for current user identification.
 *
 * @api
 * @since 100.0.2
 */
interface UserContextInterface
{
    /**#@+
     * User type
     */
    public const USER_TYPE_INTEGRATION = 1;
    public const USER_TYPE_ADMIN = 2;
    public const USER_TYPE_CUSTOMER = 3;
    public const USER_TYPE_GUEST = 4;
    /**#@-*/

    /**
     * Identify current user ID.
     *
     * @return int|null
     */
    public function getUserId();

    /**
     * Retrieve current user type.
     *
     * @return int|null
     */
    public function getUserType();
}
