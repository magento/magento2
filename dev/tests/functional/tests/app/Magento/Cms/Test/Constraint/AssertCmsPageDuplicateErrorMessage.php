<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Page\Adminhtml\CmsPageIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Fixture\CmsPage;

/**
 * Verify that page has not been created.
 */
class AssertCmsPageDuplicateErrorMessage extends AbstractConstraint
{
    /**
     * Text title of the error message to be checked.
     */
    const ERROR_MESSAGE_TITLE = 'The value specified in the URL Key field would generate a URL that already exists.';

    /**
     * Verify that page has not been created.
     *
     * @param CmsPageIndex $cmsIndex
     * @param CmsPage $cmsPage
     * @return void
     */
    public function processAssert(CmsPageIndex $cmsIndex, CmsPage $cmsPage)
    {
        $actualMessage = $cmsIndex->getMessagesBlock()->getErrorMessage();

        \PHPUnit_Framework_Assert::assertContains(
            self::ERROR_MESSAGE_TITLE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
        );

        \PHPUnit_Framework_Assert::assertContains(
            $cmsPage->getIdentifier(),
            $actualMessage,
            'CMS page url is not present on error message.'
            . "\nExpected: " . self::ERROR_MESSAGE_TITLE
            . "\nActual:\n" . $actualMessage
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
