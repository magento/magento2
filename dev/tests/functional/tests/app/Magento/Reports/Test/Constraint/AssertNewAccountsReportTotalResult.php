<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
