<?php

namespace Liip\CustomerHierarchy\Model;

use \Magento\Framework\Model\AbstractModel;

class Role extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Role::class);
    }
}
