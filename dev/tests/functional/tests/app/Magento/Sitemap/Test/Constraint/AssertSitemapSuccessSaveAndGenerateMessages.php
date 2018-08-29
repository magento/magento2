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
 * Class AssertSitemapSuccessSaveAndGenerateMessages
 */
class AssertSitemapSuccessSaveAndGenerateMessages extends AbstractConstraint
{
    const SUCCESS_GENERATE_MESSAGE = 'The sitemap "%s" has been generated.';

    const SUCCESS_SAVE_MESSAGE = 'You saved the sitemap.';

    /**
     * Assert that success messages is displayed after sitemap generate
     *
     * @param SitemapIndex $sitemapIndex
     * @param Sitemap $sitemap
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapIndex, Sitemap $sitemap)
    {
        $actualMessages = $sitemapIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit\Framework\Assert::assertTrue(
            in_array(self::SUCCESS_SAVE_MESSAGE, $actualMessages) &&
            in_array(sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename()), $actualMessages),
            'Wrong success messages is displayed.'
            . "\nExpected: " . sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename())
            . "\nExpected: " . self::SUCCESS_SAVE_MESSAGE
            . "\nActual messages: " . implode("\n", $actualMessages)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap success generate and save messages are present.';
    }
}
