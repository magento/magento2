<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Constraint\Module;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class AssertSuccessMessage
 */
class AssertSuccessMessage
{
    /**
     * Assert module action is successful.
     *
     * @param SetupWizard $setupWizard
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard)
    {
        $message = "Success";
        \PHPUnit_Framework_Assert::assertContains(
            $message,
            $setupWizard->getSuccessMessage()->getDisableModuleStatus(),
            'Success message is incorrect.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Success message is correct.";
    }
}