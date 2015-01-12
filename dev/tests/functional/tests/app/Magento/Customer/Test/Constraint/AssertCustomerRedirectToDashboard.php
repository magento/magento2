<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Check that after clicking to "Create account" button customer redirected to Dashboard.
 */
class AssertCustomerRedirectToDashboard extends AbstractConstraint
{
    /**
     * Dashboard Message on account index page.
     */
    const DASHBOARD_MESSAGE = 'My Dashboard';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that after clicking to "Create account" button customer redirected to Dashboard.
     *
     * @param CustomerAccountIndex $accountIndexPage
     * @return void
     */
    public function processAssert(CustomerAccountIndex $accountIndexPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::DASHBOARD_MESSAGE,
            $accountIndexPage->getTitleBlock()->getTitle(),
            'Wrong dashboard title is displayed.'
        );
    }

    /**
     * Text success save message is displayed.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer is redirected to Dashboard after registration.';
    }
}
