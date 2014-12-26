<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Install\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Mtf\Constraint\AbstractConstraint;

/**
 * Assert that selected currency symbol displays in admin.
 */
class AssertCurrencySelected extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that selected currency symbol displays on dashboard.
     *
     * @param string $currencySymbol
     * @param Dashboard $dashboardPage
     * @return void
     */
    public function processAssert($currencySymbol, Dashboard $dashboardPage)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($dashboardPage->getMainBlock()->getRevenuePrice(), $currencySymbol) !== false,
            'Selected currency symbol not displays on dashboard.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Selected currency displays in admin.';
    }
}
