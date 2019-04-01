<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Website\Grid;

/**
 * Grid collection
 */
class Collection extends \Magento\Store\Model\ResourceModel\Website\Collection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_map['fields']['store_title'] = 'store_table.name';
        $this->_map['fields']['group_title'] = 'group_table.name';
        $this->_map['fields']['name'] = 'main_table.name';
    }

    /**
     * Join website and store names
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinGroupAndStore();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        return $this->loadWithFilter($printQuery, $logQuery);
    }

    /**
     * @inheritdoc
     */
    public function joinGroupAndStore()
    {
        if (!$this->getFlag('groups_and_stores_joined')) {
            $this->_idFieldName = 'website_group_store';
            $this->getSelect()->joinLeft(
                ['group_table' => $this->getTable('store_group')],
                'main_table.website_id = group_table.website_id',
                ['group_id' => 'group_id', 'group_title' => 'name', 'group_code' => 'code']
            )->joinLeft(
                ['store_table' => $this->getTable('store')],
                'group_table.group_id = store_table.group_id',
                ['store_id' => 'store_id', 'store_title' => 'name', 'store_code' => 'code']
            );
        }

        return $this;
    }
}
