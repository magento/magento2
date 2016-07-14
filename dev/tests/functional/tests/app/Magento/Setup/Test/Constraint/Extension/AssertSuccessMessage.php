<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check extension installing is successfully.
 */
class AssertSuccessMessage extends AbstractConstraint
{
    /**
     * Assert extension installing is successfully.
     *
     * @param SetupWizard $setupWizard
     * @param Extension $extension
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, Extension $extension)
    {
        $message = "You installed:";
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Success message is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            $extension->getExtension(),
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Installed extension is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Install Extension success message is correct.";
    }
}
