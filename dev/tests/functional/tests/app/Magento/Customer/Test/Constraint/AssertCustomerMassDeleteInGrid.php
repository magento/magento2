<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerMassDeleteInGrid
 * Check that mass deleted customers availability in Customer Grid
 */
class AssertCustomerMassDeleteInGrid extends AbstractConstraint
{
    /**
     * Assert that customers which haven't been deleted are present in customer grid
     *
     * @param CustomerIndex $pageCustomerIndex
     * @param AssertCustomerInGrid $assertCustomerInGrid
     * @param int $customersQtyToDelete
     * @param Customer[] $customers
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
