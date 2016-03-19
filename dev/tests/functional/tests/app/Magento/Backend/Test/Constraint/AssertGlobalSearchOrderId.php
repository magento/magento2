<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

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
     * @return void
     */
    public function processAssert(Dashboard $dashboard, GlobalSearch $search)
    {
        $order = $search->getDataFieldConfig('query')['source']->getEntity();
        $orderId = "Order #" . $order->getId();
        $isVisibleInResult = $dashboard->getAdminPanelHeader()->isSearchResultVisible($orderId);
        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleInResult,
            'Order Id ' . $order->getId() . ' is absent in search results'
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
