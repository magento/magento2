<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after delete synonym group successful delete message appears.
 */
class AssertSynonymGroupDeleteMessage extends AbstractConstraint
{
    const DELETE_MESSAGE = 'The synonym group has been deleted.';

    /**
     * Assert that after delete Synonym Group successful delete message appears.
     *
     * @param SynonymGroupIndex $synonymGroupIndex
     * @return void
     */
    public function processAssert(SynonymGroupIndex $synonymGroupIndex)
    {
        $actualMessage = $synonymGroupIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::DELETE_MESSAGE
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
        return 'Synonym Group success delete message is present.';
    }
}
