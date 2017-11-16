<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertDateInvalidErrorMessage
 */
class AssertDateInvalidErrorMessage extends AbstractConstraint
{
    /**
     * Text value to be checked.
     */
    const INVALID_DATE_ERROR_MESSAGE = 'Make sure the To Date is later than or the same as the From Date.';

    /**
     * Assert that the message is displayed upon saving the product with an invalid date range.
     *
     * @param CatalogProductEdit $productPage
     * @return void
     */
    public function processAssert(CatalogProductEdit $productPage)
    {
        $actualMessages = $productPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertContains(
            self::INVALID_DATE_ERROR_MESSAGE,
            $actualMessages,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::INVALID_DATE_ERROR_MESSAGE
            . "\nActual:\n" . $actualMessages
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Invalid date range error message is displayed.';
    }
}
