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
     * @param array $upgrade
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, array $upgrade)
    {
        $message = "We're ready to upgrade {$upgrade['package']} to {$upgrade['version']}.";
        if (isset($upgrade['otherComponentsList'])) {
            foreach ($upgrade['otherComponentsList'] as $item) {
                $message .= "\nWe're ready to upgrade {$item['name']} to {$item['version']}.";
            }
        }
        $actualMessage = $setupWizard->getSystemUpgrade()->getUpgradeMessage();
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $actualMessage,
            "Updater application check is incorrect: \n"
            . "Expected: '$message' \n"
            . "Actual: '$actualMessage'"
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
