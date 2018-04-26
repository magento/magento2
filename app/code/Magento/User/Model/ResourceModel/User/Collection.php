<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\User;

/**
 * Admin user collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\User\Model\User::class, \Magento\User\Model\ResourceModel\User::class);
    }

    /**
     * Collection Init Select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['user_role' => $this->getTable('authorization_role')],
            'main_table.user_id = user_role.user_id AND user_role.parent_id != 0',
            []
        )->joinLeft(
            ['detail_role' => $this->getTable('authorization_role')],
            'user_role.parent_id = detail_role.role_id',
            ['role_name']
        );
    }
}
