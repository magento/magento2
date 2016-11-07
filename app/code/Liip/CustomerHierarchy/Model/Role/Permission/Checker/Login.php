<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission\Checker;

use Liip\CustomerHierarchy\Model\Role\Permission\CheckerInterface;

class Login implements CheckerInterface
{
    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return true;
    }
}
