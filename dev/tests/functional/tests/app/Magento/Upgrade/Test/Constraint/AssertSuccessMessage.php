<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Upgrade\Test\Constraint;

use Magento\Upgrade\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check upgrade is successfully
 */
class AssertSuccessMessage extends AbstractConstraint
{
    /**
     * Assert upgrade is successfully
     *
     * @param SetupWizard $setupWizard
     * @param string $package
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, $package)
    {
        $message = "You upgraded:";
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Success message is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            $package,
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Updated package is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "System Upgrade success message is correct.";
    }
}
