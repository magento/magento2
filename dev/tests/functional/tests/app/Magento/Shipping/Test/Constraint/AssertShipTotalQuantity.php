<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Constraint\AbstractAssertOrderOnFrontend;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\OrderView;
use Magento\Shipping\Test\Page\ShipmentView;

/**
 * Class AssertShipTotalQuantity
 * Assert that shipped items quantity in 'Total Quantity' is equal to data from fixture on My Account page
 */
class AssertShipTotalQuantity extends AbstractAssertOrderOnFrontend
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that shipped items quantity in 'Total Quantity' is equal to data from fixture on My Account page
     *
     * @param OrderHistory $orderHistory
     * @param OrderInjectable $order
     * @param OrderView $orderView
     * @param ShipmentView $shipmentView
     * @param array $ids
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        OrderInjectable $order,
        OrderView $orderView,
        ShipmentView $shipmentView,
        array $ids
    ) {
        $totalQty = $order->getTotalQtyOrdered();
        $this->loginCustomerAndOpenOrderPage($order->getDataFieldConfig('customer_id')['source']->getCustomer());
        $orderHistory->getOrderHistoryBlock()->openOrderById($order->getId());
        $orderView->getOrderViewBlock()->openLinkByName('Order Shipments');
        foreach ($ids['shipmentIds'] as $key => $shipmentIds) {
            \PHPUnit_Framework_Assert::assertEquals(
                $totalQty[$key],
                $shipmentView->getShipmentBlock()->getItemShipmentBlock($shipmentIds)->getTotalQty()
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
        return 'Shipped items quantity is equal to data from fixture on My Account page.';
    }
}
