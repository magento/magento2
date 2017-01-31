<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertStoreSuccessDeleteMessage
 * Assert that after store delete successful message appears
 */
class AssertStoreSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Success store delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the store view.';

    /**
     * Assert that after store delete successful message appears
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
        return 'Store success delete message is present.';
    }
}
