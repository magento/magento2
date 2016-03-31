<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertGlobalSearchCustomerName
 * Assert that customer name is present in search results
 */
class AssertGlobalSearchCustomerName extends AbstractConstraint
{
    /**
     * Assert that customer name is present in search results
     *
     * @param Dashboard $dashboard
     * @param GlobalSearch $search
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(Dashboard $dashboard, GlobalSearch $search, CustomerIndex $customerIndex)
    {
        $customer = $search->getDataFieldConfig('query')['source']->getEntity();
        $customerName = $customer->getFirstname() . " " . $customer->getLastname();
        $isVisibleInResult = $dashboard->getAdminPanelHeader()->isSearchResultVisible($customerName);
        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleInResult,
            'Customer name ' . $customerName . ' is absent in search results'
        );

        $dashboard->getAdminPanelHeader()->navigateToGrid("Customers");
        $isCustomerGridVisible = $customerIndex->getCustomerGridBlock()->isVisible();
        \PHPUnit_Framework_Assert::assertTrue(
            $isCustomerGridVisible,
            'Customer grid is not visible'
        );
        \PHPUnit_Framework_Assert::assertContains(
            (string) $customer->getId(),
            $customerIndex->getCustomerGridBlock()->getAllIds(),
            'Customer grid does not have ' . $customerName . ' in search results'
        );

    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer name is present in search results';
    }
}
