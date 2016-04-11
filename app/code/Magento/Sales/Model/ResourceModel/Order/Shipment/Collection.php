<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment;

use Magento\Sales\Api\Data\ShipmentSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Sales order shipment collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection implements ShipmentSearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_shipment_collection';

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
        $this->_init('Magento\Sales\Model\Order\Shipment', 'Magento\Sales\Model\ResourceModel\Order\Shipment');
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');

        return $this;
    }
}
