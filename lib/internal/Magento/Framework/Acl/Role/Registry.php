<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Role;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Acl role registry. Contains list of roles and their relations.
 */
class Registry extends \Laminas\Permissions\Acl\Role\Registry
{
    /**
     * Add parent to the $role node
     *
     * @param RoleInterface|string $role
     * @param array|RoleInterface|string $parents
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addParent($role, $parents)
    {
        try {
            if ($role instanceof RoleInterface) {
                $roleId = $role->getRoleId();
            } else {
                $roleId = $role;
                $role = $this->get($role);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Child Role id '{$roleId}' does not exist");
        }

        if (!is_array($parents)) {
            $parents = [$parents];
        }
        foreach ($parents as $parent) {
            try {
                if ($parent instanceof RoleInterface) {
                    $roleParentId = $parent->getRoleId();
                } else {
                    $roleParentId = $parent;
                }
                $roleParent = $this->get($roleParentId);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Parent Role id '{$roleParentId}' does not exist");
            }
            $this->roles[$roleId]['parents'][$roleParentId] = $roleParent;
            $this->roles[$roleParentId]['children'][$roleId] = $role;
        }
        return $this;
    }
}
