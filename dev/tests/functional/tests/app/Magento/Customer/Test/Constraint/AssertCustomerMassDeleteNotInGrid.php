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
 * Class AssertCustomerMassDeleteNotInGrid
 * Check that mass deleted customers are not in customer's grid
 */
class AssertCustomerMassDeleteNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that mass deleted customers are not in customer's grid
     *
     * @param CustomerIndex $customerIndexPage
     * @param AssertCustomerNotInGrid $assertCustomerNotInGrid
     * @param int $customersQtyToDelete
     * @param CustomerInjectable[] $customers
     * @return void
     */
    public function processAssert(
        CustomerIndex $customerIndexPage,
        AssertCustomerNotInGrid $assertCustomerNotInGrid,
        $customersQtyToDelete,
        $customers
    ) {
        for ($i = 0; $i < $customersQtyToDelete; $i++) {
            $assertCustomerNotInGrid->processAssert($customers[$i], $customerIndexPage);
        }
    }

    /**
     * Success message if Customer not in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Deleted customers are absent in Customer grid.';
    }
}
