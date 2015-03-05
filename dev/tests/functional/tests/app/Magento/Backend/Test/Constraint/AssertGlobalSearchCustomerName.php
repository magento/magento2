<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
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
     * @return void
     */
    public function processAssert(Dashboard $dashboard, GlobalSearch $search)
    {
        $customer = $search->getDataFieldConfig('query')['source']->getEntity();
        $customerName = $customer->getFirstname() . " " . $customer->getLastname();
        $isVisibleInResult = $dashboard->getAdminPanelHeader()->isSearchResultVisible($customerName);
        \PHPUnit_Framework_Assert::assertTrue(
            $isVisibleInResult,
            'Customer name ' . $customerName . ' is absent in search results'
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
