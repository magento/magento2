<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\ImportExport\Test\Page\Adminhtml\AdminImportIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check error message list after check data fail.
 */
class AssertImportCheckDataErrorMessagesList extends AbstractConstraint
{
    /* Errors parts pattern. */
    const ERROR_ATTRIBUTE_PATTERN = 'Value for \'%s\' attribute';
    const ERROR_ROWS_PATTERN = 'in row(s): %d';
    /* end */

    /**
     * Assert that error message is present.
     *
     * @param array $errors
     * @param AdminImportIndex $adminImportIndex
     * @return void
     */
    public function processAssert(array $errors, AdminImportIndex $adminImportIndex)
    {
        $messages = $adminImportIndex->getImportResult()->getErrorsList();

        \PHPUnit_Framework_Assert::assertNotFalse($messages, 'Errors messages block is absent.');
        \PHPUnit_Framework_Assert::assertNotEmpty($messages, 'Errors messages is absent.');

        foreach ($messages as $message) {
            foreach ($errors as $error) {
                \PHPUnit_Framework_Assert::assertContains(
                    sprintf(static::ERROR_ATTRIBUTE_PATTERN, $error['attribute']),
                    $message,
                    'Attribute name is absent in error message.'
                );
                \PHPUnit_Framework_Assert::assertContains(
                    sprintf(static::ERROR_ROWS_PATTERN, $error['rows']),
                    $message,
                    'Count of rows is not contained is the message.'
                );
            }
        }
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute with error contains in message. '
            . 'Count rows with errors equals count rows in the test variation.';
    }
}
