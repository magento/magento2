<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerMassDeleteInGrid
 * Check that mass deleted customers availability in Customer Grid
 */
class AssertCustomerMassDeleteInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that customers which haven't been deleted are present in customer grid
     *
     * @param CustomerIndex $pageCustomerIndex
     * @param AssertCustomerInGrid $assertCustomerInGrid
     * @param int $customersQtyToDelete
     * @param CustomerInjectable[] $customers
     * @return void
     */
    public function processAssert(
        CustomerIndex $pageCustomerIndex,
        AssertCustomerInGrid $assertCustomerInGrid,
        $customersQtyToDelete,
        $customers
    ) {
        $customers = array_slice($customers, $customersQtyToDelete);
        foreach ($customers as $customer) {
            $assertCustomerInGrid->processAssert($customer, $pageCustomerIndex);
        }
    }

    /**
     * Text success exist Customer in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customers are present in Customer grid.';
    }
}
