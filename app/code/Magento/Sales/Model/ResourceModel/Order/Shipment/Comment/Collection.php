<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment\Comment;

use Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Comment\Collection\AbstractCollection;

/**
 * Flat sales order shipment comments collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection implements ShipmentCommentSearchResultInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_comment_collection';

    /**
     * Event object
     *
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
