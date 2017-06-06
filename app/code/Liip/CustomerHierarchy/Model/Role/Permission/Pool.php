<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission;

use Liip\CustomerHierarchy\Model\Role\PermissionInterface;

class Pool implements PoolInterface
{
    /**
     * @var PermissionInterface[]
     */
    protected $permissions;

    /**
     * @param PermissionInterface[] $permissions
     */
    public function __construct(array $permissions)
    {
        foreach ($permissions as $permission) {
            $this->addChecker($permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        return $this->permissions[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->permissions;
    }

    /**
     * @param PermissionInterface $permission
     */
    private function addChecker(PermissionInterface $permission)
    {
        $this->permissions[$permission->getCode()] = $permission;
    }
}
