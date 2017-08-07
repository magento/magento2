<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Store;

/**
 * Stores collection
 *
 * @api
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'store_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'store_collection';

    /**
     *  Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setFlag('load_default_store', false);
        $this->_init(\Magento\Store\Model\Store::class, \Magento\Store\Model\ResourceModel\Store::class);
    }

    /**
     * Set flag for load default (admin) store
     *
     * @param bool $loadDefault
     * @return $this
     */
    public function setLoadDefault($loadDefault)
    {
        $this->setFlag('load_default_store', (bool)$loadDefault);
        return $this;
    }

    /**
     * Is load default (admin) store
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLoadDefault()
    {
        return $this->getFlag('load_default_store');
    }

    /**
     * Add disable default store filter to collection
     *
     * @return $this
     */
    public function setWithoutDefaultFilter()
    {
        $this->addFieldToFilter('main_table.store_id', ['gt' => 0]);
        return $this;
    }

    /**
     * Add filter by group id.
     * Group id can be passed as one single value or array of values.
     *
     * @param int|array $groupId
     * @return $this
     */
    public function addGroupFilter($groupId)
    {
        return $this->addFieldToFilter('main_table.group_id', ['in' => $groupId]);
    }

    /**
     * Add filter by status to collection
     *
     * @param int $status
     * @return $this
     * @since 100.1.0
     */
    public function addStatusFilter($status)
    {
        return $this->addFieldToFilter('main_table.is_active', ['eq' => $status]);
    }

    /**
     * Add store id(s) filter to collection
     *
     * @param int|array $store
     * @return $this
     */
    public function addIdFilter($store)
    {
        return $this->addFieldToFilter('main_table.store_id', ['in' => $store]);
    }

    /**
     * Add filter by website to collection
     *
     * @param int|array $website
     * @return $this
     */
    public function addWebsiteFilter($website)
    {
        return $this->addFieldToFilter('main_table.website_id', ['in' => $website]);
    }

    /**
     * Add root category id filter to store collection
     *
     * @param int|array $category
     * @return $this
     */
    public function addCategoryFilter($category)
    {
        if (!is_array($category)) {
            $category = [$category];
        }
        return $this->loadByCategoryIds($category);
    }

    /**
     * Convert items array to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('store_id', 'name');
    }

    /**
     * Convert items array to hash for select options
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('store_id', 'name');
    }

    /**
     * Load collection data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->getLoadDefault()) {
            $this->setWithoutDefaultFilter();
        }

        if (!$this->isLoaded()) {
            $this->addOrder(
                'CASE WHEN main_table.store_id = 0 THEN 0 ELSE 1 END',
                \Magento\Framework\DB\Select::SQL_ASC
            )->addOrder(
                'main_table.sort_order',
                \Magento\Framework\DB\Select::SQL_ASC
            )->addOrder(
                'main_table.name',
                \Magento\Framework\DB\Select::SQL_ASC
            );
        }
        return parent::load($printQuery, $logQuery);
    }

    /**
     * Add root category id filter to store collection
     *
     * @param array $categories
     * @return $this
     */
    public function loadByCategoryIds(array $categories)
    {
        $this->addRootCategoryIdAttribute();
        $this->addFieldToFilter('group_table.root_category_id', ['in' => $categories]);

        return $this;
    }

    /**
     * Add store root category data to collection
     *
     * @return $this
     */
    public function addRootCategoryIdAttribute()
    {
        if (!$this->getFlag('store_group_table_joined')) {
            $this->getSelect()->join(
                ['group_table' => $this->getTable('store_group')],
                'main_table.group_id = group_table.group_id',
                ['root_category_id']
            );
            $this->setFlag('store_group_table_joined', true);
        }

        return $this;
    }
}
