<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSitemapSuccessSaveMessage
 */
class AssertSitemapSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const SUCCESS_MESSAGE = 'The sitemap has been saved.';

    /**
     * Assert that success message is displayed after sitemap save
     *
     * @param SitemapIndex $sitemapPage
     * @return void
     */
    public function processAssert(SitemapIndex $sitemapPage)
    {
        $actualMessage = $sitemapPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
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
        return 'Sitemap success create message is present.';
    }
}
