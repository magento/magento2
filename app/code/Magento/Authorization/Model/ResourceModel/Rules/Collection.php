<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Rules;

/**
 * Rules collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
        $this->_init('Magento\Authorization\Model\Rules', 'Magento\Authorization\Model\ResourceModel\Rules');
    }

    /**
     * Get rules by role id
     *
     * @param int $roleId
     * @return $this
     */
    public function getByRoles($roleId)
    {
        $this->addFieldToFilter('role_id', (int)$roleId);
        return $this;
    }

    /**
     * Sort by length
     *
     * @return $this
     */
    public function addSortByLength()
    {
        $length = $this->getConnection()->getLengthSql('{{resource_id}}');
        $this->addExpressionFieldToSelect('length', $length, 'resource_id');
        $this->getSelect()->order('length ' . \Magento\Framework\DB\Select::SQL_DESC);

        return $this;
    }
}
