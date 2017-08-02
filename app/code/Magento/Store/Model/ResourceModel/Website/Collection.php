<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Website;

/**
 * Websites collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Map field to alias
     *
     * @var array
     * @since 2.0.0
     */
    protected $_map = ['fields' => ['website_id' => 'main_table.website_id']];

    /**
     * Define resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setFlag('load_default_website', false);
        $this->_init(\Magento\Store\Model\Website::class, \Magento\Store\Model\ResourceModel\Website::class);
    }

    /**
     * Apply custom filtering
     *
     * @return void
     * @since 2.0.0
     */
    protected function _renderFiltersBefore()
    {
        if (!$this->getLoadDefault()) {
            $this->getSelect()->where('main_table.website_id > ?', 0);
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Set flag for load default (admin) website
     *
     * @param bool $loadDefault
     * @return $this
     * @since 2.0.0
     */
    public function setLoadDefault($loadDefault)
    {
        $this->setFlag('load_default_website', (bool)$loadDefault);
        return $this;
    }

    /**
     * Is load default (admin) website
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getLoadDefault()
    {
        return $this->getFlag('load_default_website');
    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('website_id', 'name');
    }

    /**
     * Convert items array to hash for select options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('website_id', 'name');
    }

    /**
     * Add website filter to collection
     *
     * @param int $ids|array
     * @return $this
     * @since 2.0.0
     */
    public function addIdFilter($ids)
    {
        if (is_array($ids)) {
            if (empty($ids)) {
                $this->addFieldToFilter('website_id', null);
            } else {
                $this->addFieldToFilter('website_id', ['in' => $ids]);
            }
        } else {
            $this->addFieldToFilter('website_id', $ids);
        }
        return $this;
    }

    /**
     * Load collection data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return $this
     * @since 2.0.0
     */
    public function load($printQuery = false, $logQuery = false)
    {
        $this->unshiftOrder('main_table.name', \Magento\Framework\DB\Select::SQL_ASC)       // website name SECOND
            ->unshiftOrder('main_table.sort_order', \Magento\Framework\DB\Select::SQL_ASC); // website sort order FIRST

        return parent::load($printQuery, $logQuery);
    }

    /**
     * Join group and store info from appropriate tables.
     * Defines new _idFiledName as 'website_group_store' bc for
     * one website can be more then one row in collection.
     * Sets extra combined ordering by group's name, defined
     * sort ordering and store's name.
     *
     * @return $this
     * @since 2.0.0
     */
    public function joinGroupAndStore()
    {
        if (!$this->getFlag('groups_and_stores_joined')) {
            $this->_idFieldName = 'website_group_store';
            $this->getSelect()->joinLeft(
                ['group_table' => $this->getTable('store_group')],
                'main_table.website_id = group_table.website_id',
                ['group_id' => 'group_id', 'group_title' => 'name']
            )->joinLeft(
                ['store_table' => $this->getTable('store')],
                'group_table.group_id = store_table.group_id',
                ['store_id' => 'store_id', 'store_title' => 'name']
            );
            $this->addOrder('group_table.name', \Magento\Framework\DB\Select::SQL_ASC)       // store name
                ->addOrder(
                    'CASE WHEN store_table.store_id = 0 THEN 0 ELSE 1 END',
                    \Magento\Framework\DB\Select::SQL_ASC
                ) // view is admin
                ->addOrder('store_table.sort_order', \Magento\Framework\DB\Select::SQL_ASC) // view sort order
                ->addOrder('store_table.name', \Magento\Framework\DB\Select::SQL_ASC)       // view name
            ;
        }
        return $this;
    }

    /**
     * Adding filter by group id or array of ids but only if
     * tables with appropriate information were joined before.
     *
     * @param int|array $groupIds
     * @return $this
     * @since 2.0.0
     */
    public function addFilterByGroupIds($groupIds)
    {
        if ($this->getFlag('groups_and_stores_joined')) {
            $this->addFieldToFilter('group_table.group_id', $groupIds);
        }
        return $this;
    }
}
