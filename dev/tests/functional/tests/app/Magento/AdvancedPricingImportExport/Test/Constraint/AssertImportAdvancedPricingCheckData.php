<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\ImportExport\Test\Fixture\ImportData;

/**
 * Check message after check data click.
 */
class AssertImportAdvancedPricingCheckData extends AbstractConstraint
{
    /**
     * Success validation result message
     */
    const RESULT_MESSAGE = 'Checked rows: %s, checked entities: %s, invalid rows: 0, total errors: 0';

    /**
     * Assert that validation result message is correct.
     *
     * @param AdminImportIndex $adminImportIndex
     * @param ImportData $import
     */
    public function processAssert(AdminImportIndex $adminImportIndex, ImportData $import)
    {
        $rowsCount = $import->getDataFieldConfig('import_file')['source']->getValue()['template']['count'];
        $entitiesCount = count($import->getDataFieldConfig('import_file')['source']->getEntities());

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
