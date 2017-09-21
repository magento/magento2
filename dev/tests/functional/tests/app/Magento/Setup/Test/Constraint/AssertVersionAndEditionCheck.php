<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that package and version is correct
 */
class AssertVersionAndEditionCheck extends AbstractConstraint
{
    /**
     * Assert that package and version is correct
     *
     * @param SetupWizard $setupWizard
     * @param string $package
     * @param string $version
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, $package, $version)
    {
        $message = "We're ready to upgrade $package to $version";
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getSystemUpgrade()->getUpgradeMessage(),
            'Updater application check is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "System Upgrade edition and version check passed.";
    }
}
