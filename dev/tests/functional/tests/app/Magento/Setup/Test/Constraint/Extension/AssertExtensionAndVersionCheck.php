<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Check that extension and version is correct.
 */
class AssertExtensionAndVersionCheck extends AbstractConstraint
{
    /**#@+
     * Types of the job on extensions.
     */
    const TYPE_INSTALL = 1;
    const TYPE_UNINSTALL = 2;
    const TYPE_UPDATE = 3;
    /*#@-*/
    
    /**
     * Assert that extension and version is correct.
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
                $message = "We're ready to install " . $extension->getExtensionName()
                    . " to " . $extension->getVersion();
                break;

            case self::TYPE_UNINSTALL:
                $message = "We're ready to uninstall " . $extension->getExtensionName();
                break;

            case self::TYPE_UPDATE:
                $message = "We're ready to update " . $extension->getExtensionName()
                    . " to " . $extension->getVersionToUpdate();
                break;

            default:
                $message = '';
        }

        \PHPUnit\Framework\Assert::assertContains(
            $message,
            $setupWizard->getUpdaterExtension()->getMessage(),
            'Extension name and version check is incorrect.'
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
