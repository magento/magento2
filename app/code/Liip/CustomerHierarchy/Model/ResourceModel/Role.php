<?php

namespace Liip\CustomerHierarchy\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Role extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(\Liip\CustomerHierarchy\Setup\InstallSchema::CUSTOMER_ROLES_TABLE, 'entity_id');
    }

    /**
     * @param int $roleId
     * @return array
     */
    public function getPermissionsByRoleId($roleId)
    {
        $select = $this->getConnection()->select()->from(
            \Liip\CustomerHierarchy\Setup\InstallSchema::CUSTOMER_PERMISSIONS_TABLE,
            ['code', 'value']
        )->where('role_id IN (?)', $roleId);

        return $this->getConnection()->fetchAll($select);
    }
}
