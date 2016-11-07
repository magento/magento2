<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission\Checker;

use Liip\CustomerHierarchy\Model\Role\Permission\CheckerInterface;

interface PoolInterface
{
    /**
     * @param string $type
     * @return CheckerInterface
     */
    public function get(string $type): CheckerInterface;
}
