<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission;

use Liip\CustomerHierarchy\Model\Role\PermissionInterface;

interface PoolInterface
{
    /**
     * @param string $code
     * @return PermissionInterface
     */
    public function get($code);

    /**
     * @return PermissionInterface[]
     */
    public function getAll();
}
