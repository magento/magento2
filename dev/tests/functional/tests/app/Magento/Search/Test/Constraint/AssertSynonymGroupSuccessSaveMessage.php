<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after save block successful message appears.
 */
class AssertSynonymGroupSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_SAVE_MESSAGE = 'You saved the synonym group.';

    /**
     * Assert that after save Synonym Group successful message appears.
     *
     * @param SynonymGroupIndex $synonymGroupIndex
     * @return void
     */
    public function processAssert(SynonymGroupIndex $synonymGroupIndex)
    {
        $actualMessage = $synonymGroupIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_SAVE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_SAVE_MESSAGE
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
        return 'Synonym Group success create message is present.';
    }
}
