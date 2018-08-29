<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message is displayed after search terms were mass deleted.
 */
class AssertSearchTermSuccessMassDeleteMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_MESSAGE = 'Total of %d record(s) were deleted.';

    /**
     * Assert that success message is displayed after search terms were mass deleted.
     *
     * @param array $searchTerms
     * @param CatalogSearchIndex $indexPage
     * @return void
     */
    public function processAssert(array $searchTerms, CatalogSearchIndex $indexPage)
    {
        $actualMessage = $indexPage->getMessagesBlock()->getSuccessMessage();
        $expectedMessage = sprintf(self::SUCCESS_MESSAGE, count($searchTerms));
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $expectedMessage
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
        return 'Search terms success delete message is present.';
    }
}
