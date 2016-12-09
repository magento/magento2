<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission;

use Liip\CustomerHierarchy\Api\PermissionManagerInterface;
use Liip\CustomerHierarchy\Model\Role\Permission\PoolInterface;

class Manager implements PermissionManagerInterface
{
    /**
     * @var PoolInterface
     */
    private $permissionCheckersPool;

    public function __construct(
        PoolInterface $permissionCheckersPool
    ) {
        $this->permissionCheckersPool = $permissionCheckersPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        return $this->permissionCheckersPool->getAll();
    }
}
