<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Authorization\Model;

/**
 * Interface for current user identification.
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
     */
    public function getUserId();

    /**
     * Retrieve current user type.
     *
     * @return int|null
     */
    public function getUserType();
}
