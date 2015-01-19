<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\CustomerAccounts;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertNewAccountsReportTotalResult
 * Assert that new account total result is equals to data from dataSet
 */
class AssertNewAccountsReportTotalResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that new account total result is equals to data from dataSet
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
        return 'New account total result is equals to data from dataSet.';
    }
}
