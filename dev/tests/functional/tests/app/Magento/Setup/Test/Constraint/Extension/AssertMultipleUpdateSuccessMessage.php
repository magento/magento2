<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Constraint\Extension;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class AssertMultipleUpdateSuccessMessage
 */
class AssertMultipleUpdateSuccessMessage extends AbstractConstraint
{
    /**
     * Assert update of extensions is successful.
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
