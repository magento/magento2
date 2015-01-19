<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteDeletedMessage
 * Assert that delete message is displayed
 */
class AssertUrlRewriteDeletedMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message that displayed after delete url rewrite
     */
    const SUCCESS_DELETE_MESSAGE = 'The URL Rewrite has been deleted.';

    /**
     * Assert that delete message is displayed
     *
     * @param UrlRewriteIndex $index
     * @return void
     */
    public function processAssert(UrlRewriteIndex $index)
    {
        $actualMessage = $index->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Url rewrite delete message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Url rewrite delete message is displayed.';
    }
}
