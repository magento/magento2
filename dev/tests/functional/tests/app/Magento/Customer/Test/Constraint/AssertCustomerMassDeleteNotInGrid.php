<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
