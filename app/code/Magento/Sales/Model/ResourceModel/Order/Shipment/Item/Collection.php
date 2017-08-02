<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment\Item;

/**
 * Flat sales order shipment items collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_shipment_item_collection';

    /**
     * Event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'order_shipment_item_collection';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Shipment\Item::class,
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Item::class
        );
    }

    /**
     * Set shipment filter
     *
     * @param int $shipmentId
     * @return $this
     * @since 2.0.0
     */
    public function setShipmentFilter($shipmentId)
    {
        $this->addFieldToFilter('parent_id', $shipmentId);
        return $this;
    }
}
