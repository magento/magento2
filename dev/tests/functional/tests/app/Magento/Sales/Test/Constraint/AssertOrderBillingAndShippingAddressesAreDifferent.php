<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Billing Address different than Shipping Address on order page.
 */
class AssertOrderBillingAndShippingAddressesAreDifferent extends AbstractConstraint
{
    /**
     * Assert that Order Billing Address different than Shipping Address on order page.
     *
     * @param SalesOrderView $salesOrderView
     * @param string $orderId
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        $orderId
    ) {
        $salesOrderView->open(['order_id' => $orderId]);
        $orderBillingAddress = $salesOrderView->getAddressesBlock()->getCustomerBillingAddress();
        $orderShippingAddress = $salesOrderView->getAddressesBlock()->getCustomerShippingAddress();

        \PHPUnit_Framework_Assert::assertNotEquals(
            $orderBillingAddress,
            $orderShippingAddress,
            "Billing and shipping addresses on order page are the same but shouldn't."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Billing and Shipping addresses are different on order page.';
    }
}
