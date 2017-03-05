<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Click import and check success message.
 */
class AssertImportSuccessMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const SUCCESS_MESSAGE = 'Import successfully done';

    /**
     * Assert that validation result message is correct.
     *
     * @param AdminImportIndex $adminImportIndex
     * @return void
     */
    public function processAssert(AdminImportIndex $adminImportIndex)
    {
        $adminImportIndex->getImportResult()->clickImportButton();
        $validationMessage = $adminImportIndex->getImportResult()->getImportResultMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $validationMessage,
            'Wrong validation result is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $validationMessage
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Displayed import result is correct.';
    }
}
