<?php

namespace Liip\CustomerHierarchy\Model\ResourceModel\Role;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            \Liip\CustomerHierarchy\Model\Role::class,
            \Liip\CustomerHierarchy\Model\ResourceModel\Role::class
        );
    }
}
