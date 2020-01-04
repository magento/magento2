<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LoginAsCustomer\Model\ResourceModel\Login\Grid;

/**
 * LoginAsCustomer collection
 */
class Collection extends \Magento\LoginAsCustomer\Model\ResourceModel\Login\Collection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_map['fields']['email'] = 'c.email';
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['c' => $this->getTable('customer_entity')],
                'c.entity_id = main_table.customer_id',
                ['email']
            )->joinLeft(
                ['a' => $this->getTable('admin_user')],
                'a.user_id = main_table.admin_id',
                ['username']
            );
        return $this;
    }
}
