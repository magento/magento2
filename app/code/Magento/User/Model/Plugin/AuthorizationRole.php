<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Plugin;

use Magento\Authorization\Model\Role;
use Magento\User\Model\ResourceModel\User;

/**
 * Plugin for authorization role model
 */
class AuthorizationRole
{
    /**
     * Initialize dependencies
     *
     * @param User $userResourceModel
     */
    public function __construct(
        protected readonly User $userResourceModel
    ) {
    }

    /**
     * Update role users ACL.
     *
     * @param Role $subject
     * @param Role $result
     * @return Role
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Role $subject, Role $result)
    {
        $this->userResourceModel->updateRoleUsersAcl($subject);
        return $result;
    }
}
