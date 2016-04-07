<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\CustomerAccounts;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertNewAccountsReportTotalResult
 * Assert that new account total result is equals to data from dataset
 */
class AssertNewAccountsReportTotalResult extends AbstractConstraint
{
    /**
     * Assert that new account total result is equals to data from dataset
     *
     * @param CustomerAccounts $customerAccounts
     * @param string $total
     * @return void
     */
    public function processAssert(CustomerAccounts $customerAccounts, $total)
    {
        $totalForm = $customerAccounts->getGridBlock()->getTotalResults();
        \PHPUnit_Framework_Assert::assertEquals($total, $totalForm);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'New account total result is equals to data from dataset.';
    }
}
