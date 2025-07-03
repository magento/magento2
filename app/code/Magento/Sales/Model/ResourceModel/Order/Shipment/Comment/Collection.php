<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment\Comment;

use Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Comment\Collection\AbstractCollection;

/**
 * Flat sales order shipment comments collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements ShipmentCommentSearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_comment_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_shipment_comment_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Shipment\Comment::class,
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment::class
        );
    }

    /**
     * Set shipment filter
     *
     * @param int $shipmentId
     * @return $this
     */
    public function setShipmentFilter($shipmentId)
    {
        return $this->setParentFilter($shipmentId);
    }
}
