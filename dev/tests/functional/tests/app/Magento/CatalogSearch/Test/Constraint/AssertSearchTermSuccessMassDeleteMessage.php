<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermSuccessMassDeleteMessage
 * Assert that success message is displayed after search terms were mass deleted
 */
class AssertSearchTermSuccessMassDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Text value to be checked
     */
    const SUCCESS_MESSAGE = 'Total of %d record(s) were deleted';

    /**
     * Assert that success message is displayed after search terms were mass deleted
     *
     * @param array $searchTerms
     * @param CatalogSearchIndex $indexPage
     * @return void
     */
    public function processAssert(array $searchTerms, CatalogSearchIndex $indexPage)
    {
        $actualMessage = $indexPage->getMessagesBlock()->getSuccessMessages();
        $successMessages = sprintf(self::SUCCESS_MESSAGE, count($searchTerms));
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessages,
            $actualMessage,
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Search terms success delete message is present.';
    }
}
