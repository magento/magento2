<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Resource\Type;

/**
 * GoogleShopping Item Types collection
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Type', 'Magento\GoogleShopping\Model\Resource\Type');
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinAttributeSet();
        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $paginatorAdapter = new \Zend_Paginator_Adapter_DbSelect($this->getSelect());
        return $paginatorAdapter->getCountSelect();
    }

    /**
     * Add total count of Items for each type
     *
     * @return $this
     */
    public function addItemsCount()
    {
        $this->getSelect()->joinLeft(
            ['items' => $this->getTable('googleshopping_items')],
            'main_table.type_id=items.type_id',
            ['items_total' => new \Zend_Db_Expr('COUNT(items.item_id)')]
        )->group(
            'main_table.type_id'
        );
        return $this;
    }

    /**
     * Add country ISO filter to collection
     *
     * @param string $iso Two-letter country ISO code
     * @return $this
     */
    public function addCountryFilter($iso)
    {
        $this->getSelect()->where('target_country=?', $iso);
        return $this;
    }

    /**
     * Join Attribute Set data
     *
     * @return $this
     */
    protected function _joinAttributeSet()
    {
        $this->getSelect()->join(
            ['set' => $this->getTable('eav_attribute_set')],
            'main_table.attribute_set_id=set.attribute_set_id',
            ['attribute_set_name' => 'set.attribute_set_name']
        );
        return $this;
    }
}
