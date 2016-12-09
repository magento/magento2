<?php

namespace Liip\CustomerHierarchy\Model\Role\Permission;

class PriceView extends \Liip\CustomerHierarchy\Model\RolePermission
{
    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return __('Can view price');
    }
}
