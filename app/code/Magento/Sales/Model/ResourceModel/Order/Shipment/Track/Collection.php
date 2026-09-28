<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment\Track;

use Magento\Sales\Api\Data\ShipmentTrackSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order shipment tracks collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements ShipmentTrackSearchResultInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_track_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_shipment_track_collection';

    /**
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
        $this->_init(
            \Magento\Sales\Model\Order\Shipment\Track::class,
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Track::class
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
        $this->addFieldToFilter('parent_id', $shipmentId);
        return $this;
    }
}
