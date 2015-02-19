<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Constraint\AbstractAssertOrderOnFrontend;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Shipping\Test\Page\ShipmentView;

/**
 * Class AssertShipTotalQuantity
 * Assert that shipped items quantity in 'Total Quantity' is equal to data from fixture on My Account page
 */
class AssertShipTotalQuantity extends AbstractAssertOrderOnFrontend
{
    /**
     * Assert that shipped items quantity in 'Total Quantity' is equal to data from fixture on My Account page
     *
     * @param OrderHistory $orderHistory
     * @param OrderInjectable $order
     * @param CustomerOrderView $customerOrderView
     * @param ShipmentView $shipmentView
     * @param array $ids
     * @return void
     */
    public function processAssert(
        OrderHistory $orderHistory,
        OrderInjectable $order,
        CustomerOrderView $customerOrderView,
        ShipmentView $shipmentView,
        array $ids
    ) {
        $totalQty = $order->getTotalQtyOrdered();
        $this->loginCustomerAndOpenOrderPage($order->getDataFieldConfig('customer_id')['source']->getCustomer());
        $orderHistory->getOrderHistoryBlock()->openOrderById($order->getId());
        $customerOrderView->getOrderViewBlock()->openLinkByName('Order Shipments');
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
