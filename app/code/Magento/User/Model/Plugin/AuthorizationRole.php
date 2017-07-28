<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Plugin;

use Magento\Authorization\Model\Role;

/**
 * Plugin for authorization role model
 * @since 2.0.0
 */
class AuthorizationRole
{
    /**
     * @var \Magento\User\Model\ResourceModel\User
     * @since 2.0.0
     */
    protected $userResourceModel;

    /**
     * Initialize dependencies
     *
     * @param \Magento\User\Model\ResourceModel\User $userResourceModel
     * @since 2.0.0
     */
    public function __construct(\Magento\User\Model\ResourceModel\User $userResourceModel)
    {
        $this->userResourceModel = $userResourceModel;
    }

    /**
     * Update role users ACL.
     *
     * @param Role $subject
     * @param Role $result
     * @return Role
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterSave(Role $subject, Role $result)
    {
        $this->userResourceModel->updateRoleUsersAcl($subject);
        return $result;
    }
}
