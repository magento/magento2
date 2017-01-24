<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Constraint;

use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert indexers status after change action.
 */
class AssertUpdateByScheduleSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Text of save success message.
     */
    const SUCCESS_SAVE_MESSAGE = '%s indexer(s) are in "Update by Schedule" mode.';

    /**
     * Assert attribute Update by Schedule.
     *
     * @param IndexManagement $indexManagement
     * @param array $indexers
     * @return void
     */
    public function processAssert(IndexManagement $indexManagement, array $indexers)
    {
        $actualMessage = $indexManagement->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_SAVE_MESSAGE, count($indexers)),
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . sprintf(self::SUCCESS_SAVE_MESSAGE, count($indexers))
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
        return 'Attribute Update by Schedule message is present.';
    }
}
