<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapSuccessDeleteMessage
 */
class AssertSitemapSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_DELETE_MESSAGE = 'The sitemap has been deleted.';

    /**
     * Assert that success message is displayed after sitemap delete
     *
     * @param SitemapIndex $sitemapPage
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapPage)
    {
        $actualMessage = $sitemapPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text of success delete sitemap assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sitemap success delete message is present.';
    }
}
