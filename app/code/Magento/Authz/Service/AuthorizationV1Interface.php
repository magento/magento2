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
namespace Magento\Authz\Service;

use Magento\Authz\Model\UserIdentifier;
use Magento\Service\Exception as ServiceException;

/**
 * Authorization service interface.
 */
interface AuthorizationV1Interface
{
    /**
     * Grant permissions to user to access the specified resources.
     *
     * @param UserIdentifier $userIdentifier
     * @param string[] $resources List of resources which should be available to the specified user.
     * @return void
     * @throws ServiceException
     */
    public function grantPermissions(UserIdentifier $userIdentifier, array $resources);

    /**
     * Grant permissions to the user to access all resources available in the system.
     *
     * @param UserIdentifier $userIdentifier
     * @return void
     * @throws ServiceException
     */
    public function grantAllPermissions(UserIdentifier $userIdentifier);

    /**
     * Check if the user has permission to access the requested resources.
     *
     * Access is prohibited if there is a lack of permissions to any of the requested resources.
     *
     * @param string|string[] $resources Single resource or a list of resources
     * @param UserIdentifier|null $userIdentifier Context of current user is used by default
     * @return bool
     * @throws ServiceException
     */
    public function isAllowed($resources, UserIdentifier $userIdentifier = null);

    /**
     * Get a list of resources available to the specified user.
     *
     * @param UserIdentifier $userIdentifier
     * @return string[]
     * @throws ServiceException
     */
    public function getAllowedResources(UserIdentifier $userIdentifier);

    /**
     * Remove user role and associated permissions.
     *
     * @param UserIdentifier $userIdentifier
     * @return void
     * @throws ServiceException
     */
    public function removePermissions(UserIdentifier $userIdentifier);
}
