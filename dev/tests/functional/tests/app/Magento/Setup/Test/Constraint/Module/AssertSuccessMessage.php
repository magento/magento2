<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Constraint\Module;

use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class AssertSuccessMessage
 *
 * Checks whether Module manipulation was succeed.
 */
class AssertSuccessMessage
{
    const SUCCESS_MESSAGE = 'Success';

    /**
     * Assert module action is successful.
     *
     * @param SetupWizard $setupWizard
     * @return void
     */
    public function processAssert(SetupWizard $setupWizard)
    {
        \PHPUnit_Framework_Assert::assertContains(
            static::SUCCESS_MESSAGE,
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
