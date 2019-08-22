<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapFailFolderSaveMessage
 */
class AssertSitemapFailFolderSaveMessage extends AbstractConstraint
{
    const FAIL_FOLDER_MESSAGE = 'Please create the specified folder "%s" before saving the sitemap.';

    /**
     * Assert that error message is displayed after creating sitemap with wrong folder
     *
     * @param SitemapIndex $sitemapPage
     * @param Sitemap $sitemap
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapPage, Sitemap $sitemap)
    {
        $actualMessage = $sitemapPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            sprintf(self::FAIL_FOLDER_MESSAGE, $sitemap->getSitemapPath()),
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::FAIL_FOLDER_MESSAGE
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
        return 'Error message after creating sitemap with wrong folder is present.';
    }
}
