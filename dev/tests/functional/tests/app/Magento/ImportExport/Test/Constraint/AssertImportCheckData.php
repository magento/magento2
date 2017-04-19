<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Constraint;

use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check message after check data click.
 */
class AssertImportCheckData extends AbstractConstraint
{
    /**
     * Success validation result message.
     */
    const RESULT_MESSAGE = 'Checked rows: %s, checked entities: %s, invalid rows: 0, total errors: 0';

    /**
     * Assert that validation result message is correct.
     *
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     * @return void
     */
    public function processAssert(AdminImportIndex $adminImportIndex, ImportData $import)
    {
        $file = $import->getDataFieldConfig('import_file')['source'];
        $rowsCount = $file->getValue()['template']['count'];
        $entitiesCount = isset($file->getValue()['template']['entities'])
            ? $file->getValue()['template']['entities']
            : count($file->getEntities());

        $message = $adminImportIndex->getMessagesBlock()->getNoticeMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::RESULT_MESSAGE, $rowsCount, $entitiesCount),
            $message,
            'Wrong validation result message is displayed.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed validation result is correct.';
    }
}
