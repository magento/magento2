<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\ResourceModel\Order\Track;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Track as ResourceOrderShipmentTrack;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection as OrderShipmentTrackCollection;
use Magento\Shipping\Model\Order\Track as ModelOrderTrack;

/**
 * Flat sales order shipment tracks collection
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Collection extends OrderShipmentTrackCollection
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            ModelOrderTrack::class,
            ResourceOrderShipmentTrack::class
        );
    }
}
