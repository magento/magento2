<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
