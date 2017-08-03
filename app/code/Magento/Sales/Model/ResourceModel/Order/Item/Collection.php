<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Item;

use \Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order payment collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends AbstractCollection implements \Magento\Sales\Api\Data\OrderItemSearchResultInterface
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_item_collection';

    /**
     * Event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'order_item_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     * @since 2.0.0
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\Order\Item::class, \Magento\Sales\Model\ResourceModel\Order\Item::class);
    }

    /**
     * Assign parent items on after collection load
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        /**
         * Assign parent items
         */
        foreach ($this as $item) {
            $this->_resource->unserializeFields($item);
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
