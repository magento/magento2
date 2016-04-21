<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;

/**
 * Class AssertGlobalSearchOrderId
 * Assert that order Id is present in search results
 */
class AssertGlobalSearchOrderId extends AbstractConstraint
{
    /**
     * Assert that order Id is present in search results
     *
     * @param Dashboard $dashboard
     * @param GlobalSearch $search
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(Dashboard $dashboard, GlobalSearch $search, OrderIndex $orderIndex)
    {
        $order = $search->getDataFieldConfig('query')['source']->getEntity();
        $orderId = "Order #" . $order->getId();
        $isVisibleInResult = $dashboard->getAdminPanelHeader()->isSearchResultVisible($orderId);
        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleInResult,
            'Order Id ' . $order->getId() . ' is absent in search results'
        );

        $dashboard->getAdminPanelHeader()->navigateToGrid("Orders");
        $isOrderGridVisible = $orderIndex->getSalesOrderGrid()->isVisible();

        \PHPUnit_Framework_Assert::assertTrue(
            $isOrderGridVisible,
            'Order grid is not visible'
        );
        \PHPUnit_Framework_Assert::assertContains(
            (string) $order->getId(),
            $orderIndex->getSalesOrderGrid()->getAllIds(),
            'Order grid does not have ' . $order->getId()  . ' in search results'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order Id is present in search results';
    }
}
