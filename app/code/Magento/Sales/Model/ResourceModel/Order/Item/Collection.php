<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Item;

/**
 * Flat sales order payment collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_item_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_item_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Item', 'Magento\Sales\Model\ResourceModel\Order\Item');
    }

    /**
     * Assign parent items on after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        /**
         * Assign parent items
         */
        foreach ($this as $item) {
            if ($item->getParentItemId()) {
                $item->setParentItem($this->getItemById($item->getParentItemId()));
            }
        }
        return $this;
    }

    /**
     * Set random items order
     *
     * @return $this
     */
    public function setRandomOrder()
    {
        $this->getConnection()->orderRand($this->getSelect());
        return $this;
    }

    /**
     * Set filter by item id
     *
     * @param mixed $item
     * @return $this
     */
    public function addIdFilter($item)
    {
        if (is_array($item)) {
            $this->addFieldToFilter('item_id', ['in' => $item]);
        } elseif ($item instanceof \Magento\Sales\Model\Order\Item) {
            $this->addFieldToFilter('item_id', $item->getId());
        } else {
            $this->addFieldToFilter('item_id', $item);
        }
        return $this;
    }

    /**
     * Filter collection by specified product types
     *
     * @param array $typeIds
     * @return $this
     */
    public function filterByTypes($typeIds)
    {
        $this->addFieldToFilter('product_type', ['in' => $typeIds]);
        return $this;
    }

    /**
     * Filter collection by parent_item_id
     *
     * @param int $parentId
     * @return $this
     */
    public function filterByParent($parentId = null)
    {
        if (empty($parentId)) {
            $this->addFieldToFilter('parent_item_id', ['null' => true]);
        } else {
            $this->addFieldToFilter('parent_item_id', $parentId);
        }
        return $this;
    }
}
