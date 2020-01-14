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
 * Class AssertSitemapFailPathSaveMessage
 */
class AssertSitemapFailPathSaveMessage extends AbstractConstraint
{
    const FAIL_PATH_MESSAGE = 'Path "/%s" is not available and cannot be used.';

    /**
     * Assert that error message is displayed after creating sitemap with wrong path
     *
     * @param SitemapIndex $sitemapPage
     * @param Sitemap $sitemap
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapPage, Sitemap $sitemap)
    {
        $actualMessage = $sitemapPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            sprintf(self::FAIL_PATH_MESSAGE, $sitemap->getSitemapFilename()),
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::FAIL_PATH_MESSAGE
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
        return 'Error message after creating sitemap with wrong path is present.';
    }
}
