<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\AdminAuthLogin;

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
