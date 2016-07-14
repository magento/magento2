<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that extension and version is correct.
 */
class AssertExtensionAndVersionCheck extends AbstractConstraint
{
    /**
     * Assert that extension and version is correct.
     *
     * @param SetupWizard $setupWizard
     * @param string $extension
     * @param string $version
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, $extension, $version)
    {
        $message = "We're ready to install $extension to $version";
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getInstallExtension()->getInstallMessage(),
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
        return "Extension name and version check passed.";
    }
}
