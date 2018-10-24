<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check error message after check data fail.
 */
class AssertImportCheckDataErrorMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const ERROR_MESSAGE = 'Data validation failed. Please fix the following errors and upload the file again.';

    /**
     * Assert that error message is present.
     *
     * @param AdminImportIndex $adminImportIndex
     * @return void
     */
    public function processAssert(AdminImportIndex $adminImportIndex)
    {
        $actualMessage = $adminImportIndex->getMessagesBlock()->getErrorMessage();

        \PHPUnit\Framework\Assert::assertNotFalse($actualMessage, 'Error message is absent.');

        \PHPUnit\Framework\Assert::assertEquals(
            static::ERROR_MESSAGE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Data check error message is present.';
    }
}
