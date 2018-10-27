<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that AdminAuthLogin page is present as the result of an expired admin session.
 */
class AssertAdminLoginPageIsAvailable extends AbstractConstraint
{
    /**
     * Assert that AdminAuthLogin page is present as the result of an expired admin session.
     *
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
     */
    public function processAssert(AdminAuthLogin $adminAuthLogin)
    {
        $adminAuthLogin->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $adminAuthLogin->getLoginBlock()->isVisible(),
            'Admin session does not expire properly.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Admin session expires properly.';
    }
}
