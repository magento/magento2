<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Plugin;

use Magento\Authorization\Model\Role;

/**
 * Plugin for authorization role model
 */
class AuthorizationRole
{
    /** @var \Magento\User\Model\ResourceModel\User */
    protected $userResourceModel;

    /**
     * Initialize dependencies
     *
     * @param \Magento\User\Model\ResourceModel\User $userResourceModel
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
     */
    public function afterSave(Role $subject, Role $result)
    {
        $this->userResourceModel->updateRoleUsersAcl($subject);
        return $result;
    }
}
