<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteSaveMessage
 * Assert that url rewrite success message is displayed
 */
class AssertUrlRewriteSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'The URL Rewrite has been saved.';

    /**
     * Assert that url rewrite success message is displayed
     *
     * @param UrlRewriteIndex $index
     * @return void
     */
    public function processAssert(UrlRewriteIndex $index)
    {
        $actualMessage = $index->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Url rewrite success message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Url rewrite success message is displayed.';
    }
}
