<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreSuccessDeleteAndBackupMessages
 * Assert that store success delete and backup messages are present
 */
class AssertStoreSuccessDeleteAndBackupMessages extends AbstractConstraint
{
    /**
     * Success backup message
     */
    const SUCCESS_BACKUP_MESSAGE = 'The database was backed up.';

    /**
     * Success store delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the store view.';

    /**
     * Assert that store success delete and backup messages are present
     *
     * @param StoreIndex $storeIndex
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex)
    {
        $actualMessages = $storeIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array(self::SUCCESS_BACKUP_MESSAGE, $actualMessages) &&
            in_array(self::SUCCESS_DELETE_MESSAGE, $actualMessages),
            'Wrong success messages are displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store view success delete and backup messages are present.';
    }
}
