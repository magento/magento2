<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission;

interface CheckerInterface
{
    /**
     * @return bool
     */
    public function isAllowed(): bool;
}
