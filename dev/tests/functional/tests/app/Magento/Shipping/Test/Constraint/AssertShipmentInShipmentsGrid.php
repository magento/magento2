<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Shipping\Test\Page\Adminhtml\ShipmentIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertShipmentInShipmentsGrid
 * Assert shipment with corresponding shipment/order ID is present in 'Shipments' with correct 'Total Quantity' field
 */
class AssertShipmentInShipmentsGrid extends AbstractConstraint
{
    /**
     * Assert shipment with corresponding shipment/order ID is present in 'Shipments' with correct total qty field
     *
     * @param ShipmentIndex $shipmentIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(ShipmentIndex $shipmentIndex, OrderInjectable $order, array $ids)
    {
        $shipmentIndex->open();
        $orderId = $order->getId();
        $totalQty = $order->getTotalQtyOrdered();
        foreach ($ids['shipmentIds'] as $key => $shipmentIds) {
            $filter = [
                'id' => $shipmentIds,
                'order_id' => $orderId
            ];
            $filterQty = [
                'total_qty_from' => $totalQty[$key],
                'total_qty_to' => $totalQty[$key],
            ];
            $shipmentIndex->getShipmentsGrid()->search($filter + $filterQty);
            \PHPUnit\Framework\Assert::assertTrue(
                $shipmentIndex->getShipmentsGrid()->isRowVisible($filter, false),
                'Shipment is absent in shipment grid on shipment index page.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipment is present in the shipment grid with correct total qty on shipment index page.';
    }
}
