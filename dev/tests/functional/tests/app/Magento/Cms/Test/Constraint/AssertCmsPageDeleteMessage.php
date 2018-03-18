<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert success delete message.
 */
class AssertCmsPageDeleteMessage extends AbstractConstraint
{
    const SUCCESS_DELETE_MESSAGE = 'The page has been deleted.';

    /**
     * Assert that success message is displayed after Cms page delete.
     *
     * @param CmsPageIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex)
    {
        $actualMessage = $cmsIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
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
        return 'Cms page success delete message is present.';
    }
}
