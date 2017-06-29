<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteSuccessDeleteMessage
 * Assert that after website delete successful message appears
 */
class AssertWebsiteSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Success website delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'You deleted the website.';

    /**
     * Assert that success message is displayed after deleting website
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
        return 'Website success delete message is present.';
    }
}
