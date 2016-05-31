<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Check application version
 */
class AssertApplicationVersion extends AbstractConstraint
{
    /**
     * Assert upgrade is successfully
     *
     * @param Dashboard $dashboard
     * @param string $version
     * @return void
     */
    public function processAssert(Dashboard $dashboard, $version)
    {
        \PHPUnit_Framework_Assert::assertContains(
            $version,
            $dashboard->getApplicationVersion()->getVersion(),
            'Application version is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Application new version is correct.";
    }
}
