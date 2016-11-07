<?php

namespace Liip\CustomerHierarchy\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Role extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(\Liip\CustomerHierarchy\Setup\InstallSchema::CUSTOMER_ROLES_TABLE, 'entity_id');
    }
}
