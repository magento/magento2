<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Role\Grid;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

/**
 * Admin role data grid collection
 * @since 2.0.0
 */
class Collection extends \Magento\Authorization\Model\ResourceModel\Role\Collection
{
    /**
     * Prepare select for load
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE);
        return $this;
    }
}
