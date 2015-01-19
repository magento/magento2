<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapSuccessGenerateMessage
 */
class AssertSitemapSuccessGenerateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_GENERATE_MESSAGE = 'The sitemap "%s" has been generated.';

    /**
     * Assert that success message is displayed after sitemap generate
     *
     * @param SitemapIndex $sitemapPage
     * @param Sitemap $sitemap
     * @return void
     */
    public function processAssert(
        SitemapIndex $sitemapPage,
        Sitemap $sitemap
    ) {
        $actualMessage = $sitemapPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename()),
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . sprintf(self::SUCCESS_GENERATE_MESSAGE, $sitemap->getSitemapFilename())
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of success create sitemap assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap success generate message is present.';
    }
}
