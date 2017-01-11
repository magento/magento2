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
 * Check that extension and version is correct for several extensions.
 */
class AssertMultipleExtensionAndVersionCheck extends AbstractConstraint
{
    /**
     * Assert that extensions and versions are correct.
     *
     * @param SetupWizard $setupWizard
     * @param Extension[] $extensions
     * @param int $type
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, array $extensions, $type)
    {
        $assertExtensionAndVersionCheck = $this->objectManager->get(AssertExtensionAndVersionCheck::class);
        foreach ($extensions as $extension) {
            $assertExtensionAndVersionCheck->processAssert($setupWizard, $extension, $type);
        }
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
