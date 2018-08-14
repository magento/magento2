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
 * Check installing of extensions is successfully.
 */
class AssertMultipleSuccessMessage extends AbstractConstraint
{
    /**
     * Assert installing of extensions is successfully.
     *
     * @param SetupWizard $setupWizard
     * @param Extension[] $extensions
     * @param int $type
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard, array $extensions, $type)
    {
        $assertSuccessMessage = $this->objectManager->get(AssertSuccessMessage::class);
        foreach ($extensions as $extension) {
            $assertSuccessMessage->processAssert($setupWizard, $extension, $type);
        }
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
