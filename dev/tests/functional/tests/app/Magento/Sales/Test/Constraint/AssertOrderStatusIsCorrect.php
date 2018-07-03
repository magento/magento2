<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that status is correct on order page in admin panel (same with value of orderStatus variable).
 */
class AssertOrderStatusIsCorrect extends AbstractConstraint
{
    /**
     * Assert that status is correct on order page in admin panel (same with value of orderStatus variable).
     *
     * @param string $status
     * @param string $orderId
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @param string|null $statusToCheck
     * @return void
     */
    public function processAssert(
        $status,
        $orderId,
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView,
        $statusToCheck = null
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $orderStatus = $statusToCheck == null ? $status : $statusToCheck;

        /** @var \Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info $infoTab */
        $infoTab = $salesOrderView->getOrderForm()->openTab('info')->getTab('info');
        \PHPUnit\Framework\Assert::assertEquals(
            $infoTab->getOrderStatus(),
            $orderStatus
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status is correct.';
    }
}
