<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerMassDeleteNotInGrid
 * Check that mass deleted customers are not in customer's grid
 */
class AssertCustomerMassDeleteNotInGrid extends AbstractConstraint
{
    /**
     * Asserts that mass deleted customers are not in customer's grid
     *
     * @param CustomerIndex $customerIndexPage
     * @param AssertCustomerNotInGrid $assertCustomerNotInGrid
     * @param int $customersQtyToDelete
     * @param Customer[] $customers
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
