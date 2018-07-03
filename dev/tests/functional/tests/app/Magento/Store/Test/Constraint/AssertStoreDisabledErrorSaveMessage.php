<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\EditStore;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after Store View save disabled error message appears.
 */
class AssertStoreDisabledErrorSaveMessage extends AbstractConstraint
{
    /**
     * Disabled error message.
     */
    const ERROR_MESSAGE = 'The default store cannot be disabled';

    /**
     * Assert that after Store View save disabled error message appears.
     *
     * @param EditStore $editStore
     * @return void
     */
    public function processAssert(EditStore $editStore)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::ERROR_MESSAGE,
            $editStore->getMessagesBlock()->getErrorMessage(),
            'Wrong error message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store View disabled error create message is present.';
    }
}
