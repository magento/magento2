<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
