<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderReleaseFailMessage
 * Assert release fail message is displayed on order index page
 */
class AssertOrderReleaseFailMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const FAIL_RELEASE_MESSAGE = 'No order(s) were released from on hold status.';

    /**
     * Assert release fail message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::FAIL_RELEASE_MESSAGE,
            $orderIndex->getMessagesBlock()->getErrorMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Release fail message is displayed on order index page.';
    }
}
