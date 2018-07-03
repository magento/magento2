<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert that bestsellers tab content on Dashboard successfully refreshed after clicking on Refreshing data button
 */
class AssertBestsellersOnDashboard extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that ordered products in bestsellers on Dashboard successfully refreshed.
     *
     * @param OrderInjectable $order
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(OrderInjectable $order, Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getStoreStatsBlock()->refreshData();
        /** @var \Magento\Backend\Test\Block\Dashboard\Tab\Products\Ordered $bestsellersGrid */
        $bestsellersGrid = $dashboard->getStoreStatsBlock()->getTab('bestsellers')->getBestsellersGrid();
        $products = $order->getEntityId()['products'];
        foreach ($products as $product) {
            \PHPUnit\Framework\Assert::assertTrue(
                $bestsellersGrid->isProductVisible($product),
                'Bestseller ' . $product->getName() . ' is not present in report grid after refresh data.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bestsellers successfully updated after Refreshing data.';
    }
}
