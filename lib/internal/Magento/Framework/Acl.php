<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Laminas\Permissions\Acl\Role\GenericRole;
use Magento\Framework\Acl\Role\Registry as RoleRegistry;

/**
 * ACL. Can be queried for relations between roles and resources.
 *
 * @api
 * @since 100.0.2
 */
class Acl extends \Laminas\Permissions\Acl\Acl
{
    /**
     * Permission level to deny access
     */
    public const RULE_PERM_DENY = 0;

    /**
     * Permission level to inherit access from parent role
     */
    public const RULE_PERM_INHERIT = 1;

    /**
     * Permission level to allow access
     */
    public const RULE_PERM_ALLOW = 2;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roleRegistry = new RoleRegistry();
    }

    /**
     * Add parent to role object
     *
     * @param GenericRole $role
     * @param GenericRole $parent
     * @return \Magento\Framework\Acl
     */
    public function addRoleParent($role, $parent)
    {
        $this->getRoleRegistry()->addParent($role, $parent);
        return $this;
    }
}
