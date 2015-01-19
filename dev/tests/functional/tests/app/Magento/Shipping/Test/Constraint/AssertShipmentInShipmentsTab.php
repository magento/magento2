<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertShipmentInShipmentsTab
 * Assert that shipment is present in the Shipments tab with correct shipped items quantity
 */
class AssertShipmentInShipmentsTab extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that shipment is present in the Shipments tab with correct shipped items quantity
     *
     * @param OrderView $orderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @param array $ids
     * @return void
     */
    public function processAssert(
        OrderView $orderView,
        OrderIndex $orderIndex,
        OrderInjectable $order,
        array $ids
    ) {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $orderView->getOrderForm()->openTab('shipments');
        $totalQty = $order->getTotalQtyOrdered();
        $totalQty = is_array($totalQty) ? $totalQty : [$totalQty];

        foreach ($ids['shipmentIds'] as $key => $shipmentId) {
            $filter = [
                'id' => $shipmentId,
                'qty_from' => $totalQty[$key],
                'qty_to' => $totalQty[$key],
            ];
            \PHPUnit_Framework_Assert::assertTrue(
                $orderView->getOrderForm()->getTabElement('shipments')->getGridBlock()->isRowVisible($filter),
                'Shipment is absent on shipments tab.'
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
        return 'Shipment is present on shipments tab.';
    }
}
