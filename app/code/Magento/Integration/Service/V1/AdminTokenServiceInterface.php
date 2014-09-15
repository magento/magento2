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

namespace Magento\Integration\Service\V1;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface providing token generation for Admins
 */
interface AdminTokenServiceInterface
{
    /**
     * Create access token for admin given the admin credentials.
     *
     * @param string $username
     * @param string $password
     * @return string Token created
     * @throws InputException For invalid input
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function createAdminAccessToken($username, $password);

    /**
     * Revoke token by admin id.
     *
     * @param int $adminId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeAdminAccessToken($adminId);
}
