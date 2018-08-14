<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Role;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

/**
 * Admin role collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Authorization\Model\Role::class, \Magento\Authorization\Model\ResourceModel\Role::class);
    }

    /**
     * Add user filter
     *
     * @param int $userId
     * @param string $userType
     * @return $this
     */
    public function setUserFilter($userId, $userType)
    {
        $this->addFieldToFilter('user_id', $userId);
        $this->addFieldToFilter('user_type', $userType);
        return $this;
    }

    /**
     * Set roles filter
     *
     * @return $this
     */
    public function setRolesFilter()
    {
        $this->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE);
        return $this;
    }

    /**
     * Convert to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('role_id', 'role_name');
    }
}
