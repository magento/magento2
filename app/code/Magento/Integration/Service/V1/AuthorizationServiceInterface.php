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

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface for integration permissions management.
 */
interface AuthorizationServiceInterface
{
    /**#@+
     * Permission type
     */
    const PERMISSION_ANONYMOUS = 'anonymous';
    const PERMISSION_SELF = 'self';
    /**#@- */

    /**
     * Grant permissions to user to access the specified resources.
     *
     * @param int $integrationId
     * @param string[] $resources List of resources which should be available to the specified user.
     * @return void
     * @throws LocalizedException
     */
    public function grantPermissions($integrationId, $resources);

    /**
     * Grant permissions to the user to access all resources available in the system.
     *
     * @param int $integrationId
     * @return void
     * @throws LocalizedException
     */
    public function grantAllPermissions($integrationId);

    /**
     * Remove role and associated permissions for the specified integration.
     *
     * @param int $integrationId
     * @return void
     * @throws LocalizedException
     */
    public function removePermissions($integrationId);
}
