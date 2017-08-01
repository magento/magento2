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
 * @since 2.0.0
 */
interface UserContextInterface
{
    /**#@+
     * User type
     */
    const USER_TYPE_INTEGRATION = 1;
    const USER_TYPE_ADMIN = 2;
    const USER_TYPE_CUSTOMER = 3;
    const USER_TYPE_GUEST = 4;
    /**#@-*/

    /**
     * Identify current user ID.
     *
     * @return int|null
     * @api
     * @since 2.0.0
     */
    public function getUserId();

    /**
     * Retrieve current user type.
     *
     * @return int|null
     * @api
     * @since 2.0.0
     */
    public function getUserType();
}
