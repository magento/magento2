<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check extension installing, updating or uninstalling is successfully.
 */
class AssertSuccessMessage extends AbstractConstraint
{
    /**#@+
     * Types of the job on extensions.
     */
    const TYPE_INSTALL = 1;
    const TYPE_UNINSTALL = 2;
    const TYPE_UPDATE = 3;
    /*#@-*/

    /**
     * Assert extension installing, updating or uninstalling is successfully.
     *
     * @param SetupWizard $setupWizard
     * @param Extension $extension
     * @param int $type
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, Extension $extension, $type)
    {
        switch ($type) {
            case self::TYPE_INSTALL:
                $message = "You installed:";
                break;

            case self::TYPE_UNINSTALL:
                $message = "You uninstalled:";
                break;

            case self::TYPE_UPDATE:
                $message = "You updated:";
                break;

            default:
                $message = '';
        }

        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Success message is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            $extension->getExtensionName(),
            $setupWizard->getSuccessMessage()->getUpdaterStatus(),
            'Extension name is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Extension Updater success message is correct.";
    }
}
