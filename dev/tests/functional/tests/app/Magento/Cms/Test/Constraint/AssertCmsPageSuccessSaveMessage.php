<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after save a CMS page "The page has been saved." successful message appears.
 */
class AssertCmsPageSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_SAVE_MESSAGE = 'The page has been saved.';

    /**
     * Assert that after save a CMS page "The page has been saved." successful message appears.
     *
     * @param CmsPageIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex)
    {
        $actualMessage = $cmsIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_SAVE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_SAVE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Success message is displayed.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message is displayed.';
    }
}
