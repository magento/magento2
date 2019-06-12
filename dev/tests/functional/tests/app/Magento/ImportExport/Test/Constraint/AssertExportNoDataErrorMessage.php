<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that error message is visible after exporting without entity attributes data.
 */
class AssertExportNoDataErrorMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const ERROR_MESSAGE = 'Error during export process occurred. Please check logs for detail';

    /**
     * Assert that error message is visible after exporting without entity attributes data.
     *
     * @param AdminExportIndex $adminExportIndex
     * @return void
     */
    public function processAssert(AdminExportIndex $adminExportIndex)
    {
        $adminExportIndex->open();
        /** @var \Magento\ImportExport\Test\Block\Adminhtml\Export\NotificationsArea $notificationsArea */
        $notificationsArea = $adminExportIndex->getNotificationsArea();
        $notificationsArea->openNotificationsDropDown();
        $actualMessage = $notificationsArea->getLatestMessage();

        \PHPUnit\Framework\Assert::assertEquals(
            self::ERROR_MESSAGE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE
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
        return 'Correct error message is displayed.';
    }
}
