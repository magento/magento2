<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreGroupSuccessDeleteMessage
 * Assert that store group success delete message is present
 */
class AssertStoreGroupSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Success store group delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the store.';

    /**
     * Assert that success message is displayed after deleting store group
     *
     * @param StoreIndex $storeIndex
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $storeIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success delete message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Store group success delete message is present.';
    }
}
