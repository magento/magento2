<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel\Group;

/**
 * Store group collection
 *
 * @api
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
        $this->setFlag('load_default_store_group', false);
        $this->_init(\Magento\Store\Model\Group::class, \Magento\Store\Model\ResourceModel\Group::class);
    }

    /**
     * Set flag for load default (admin) store
     *
     * @param boolean $loadDefault
     * @return $this
     */
    public function setLoadDefault($loadDefault)
    {
        return $this->setFlag('load_default_store_group', (bool)$loadDefault);
    }

    /**
     * Is load default (admin) store
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getLoadDefault()
    {
        return $this->getFlag('load_default_store_group');
    }

    /**
     * Add disable default store group filter to collection
     *
     * @return $this
     */
    public function setWithoutDefaultFilter()
    {
        return $this->addFieldToFilter('main_table.group_id', ['gt' => 0]);
    }

    /**
     * Filter to discard stores without views
     *
     * @return $this
     */
    public function setWithoutStoreViewFilter()
    {
        return $this->addFieldToFilter('main_table.default_store_id', ['gt' => 0]);
    }

    /**
     * Filter to discard default group and groups with assigned category
     *
     * @return $this
     */
    public function setWithoutAssignedCategoryFilter()
    {
        return $this->addFieldToFilter('main_table.root_category_id', ['eq' => 0])
            ->addFieldToFilter('main_table.group_id', ['neq' => 0]);
    }

    /**
     * Load collection data
     *
     * @return $this
     */
    public function _beforeLoad()
    {
        if (!$this->getLoadDefault()) {
            $this->setWithoutDefaultFilter();
        }
        $this->addOrder('main_table.name', self::SORT_ORDER_ASC);
        return parent::_beforeLoad();
    }

    /**
     * Convert collection items to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('group_id', 'name');
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
}
