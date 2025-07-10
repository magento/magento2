<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item;

/**
 * Flat sales order creditmemo items collection
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_item_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_creditmemo_item_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Creditmemo\Item::class,
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item::class
        );
    }

    /**
     * Set creditmemo filter
     *
     * @param int $creditmemoId
     * @return $this
     */
    public function setCreditmemoFilter($creditmemoId)
    {
        $this->addFieldToFilter('parent_id', $creditmemoId);
        return $this;
    }
}
