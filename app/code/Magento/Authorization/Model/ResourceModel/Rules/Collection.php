<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Rules;

/**
 * Rules collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Authorization\Model\Rules::class,
            \Magento\Authorization\Model\ResourceModel\Rules::class
        );
    }

    /**
     * Get rules by role id
     *
     * @param int $roleId
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addSortByLength()
    {
        $length = $this->getConnection()->getLengthSql('{{resource_id}}');
        $this->addExpressionFieldToSelect('length', $length, 'resource_id');
        $this->getSelect()->order('length ' . \Magento\Framework\DB\Select::SQL_DESC);

        return $this;
    }
}
