<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check message after check data click.
 */
class AssertImportAdvancedPricingCheckData extends AbstractConstraint
{
    /**
     * Assert that validation result message is correct.
     *
     * @param string $expectedMessage
     * @param AdminImportIndex $adminImportIndex
     * @return void
     */
    public function processAssert($expectedMessage, AdminImportIndex $adminImportIndex)
    {
        $message = $adminImportIndex->getImportResult()->getNoticeMessage();
        \PHPUnit_Framework_Assert::assertNotFalse($message, 'Validation result block is absent.');
        \PHPUnit_Framework_Assert::assertEquals(
            $expectedMessage,
            $message,
            'Wrong validation result is displayed.'
            . "\nExpected: " . $expectedMessage
            . "\nActual: " . $message
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
