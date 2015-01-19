<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreGroupSuccessDeleteMessage
 * Assert that store group success delete message is present
 */
class AssertStoreGroupSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Success store group delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'The store has been deleted.';

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
            $storeIndex->getMessagesBlock()->getSuccessMessages(),
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
