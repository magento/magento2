<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that export submitted message is visible after exporting.
 */
class AssertExportSubmittedMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const MESSAGE = 'Message is added to queue, wait to get your file soon';

    /**
     * Assert that export submitted message is visible after exporting.
     *
     * @param AdminExportIndex $adminExportIndex
     * @return void
     */
    public function processAssert(AdminExportIndex $adminExportIndex)
    {
        $actualMessage = $adminExportIndex->getMessagesBlock()->getSuccessMessage();

        \PHPUnit\Framework\Assert::assertEquals(
            self::MESSAGE,
            $actualMessage,
            'Wrong message is displayed.'
            . "\nExpected: " . self::MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Correct message is displayed.';
    }
}
