<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Resource\Role\Grid;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;

/**
 * Admin role data grid collection
 */
class Collection extends \Magento\Authorization\Model\Resource\Role\Collection
{
    /**
     * Prepare select for load
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE);
        return $this;
    }
}
