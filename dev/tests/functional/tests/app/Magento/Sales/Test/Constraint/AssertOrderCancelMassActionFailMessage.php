<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderCancelMassActionFailMessage
 * Assert cancel fail message is displayed on order index page
 */
class AssertOrderCancelMassActionFailMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const FAIL_CANCEL_MESSAGE = 'You cannot cancel the order(s).';

    /**
     * Assert cancel fail message is displayed on order index page
     *
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::FAIL_CANCEL_MESSAGE,
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
        return 'Cancel fail message is displayed on order index page.';
    }
}
