<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Verify that page has not been created.
 */
class AssertCmsPageDuplicateErrorMessage extends AbstractConstraint
{
    const ERROR_SAVE_MESSAGE = 'A page URL key for specified store already exists.';

    /**
     * Verify that page has not been created.
     *
     * @param CmsPageIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex)
    {
        $message = $cmsIndex->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_SAVE_MESSAGE,
            $message,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_SAVE_MESSAGE
            . "\nActual: " . $message
        );
    }

    /**
     * Page with duplicated identifier has not been created.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that page with duplicated identifier has not been created.';
    }
}
