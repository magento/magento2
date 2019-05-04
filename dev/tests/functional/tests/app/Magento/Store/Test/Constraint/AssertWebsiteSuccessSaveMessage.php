<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteSuccessSaveMessage
 * Assert that after Website save successful message appears
 */
class AssertWebsiteSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Success website create message
     */
    const SUCCESS_MESSAGE = 'You saved the website.';

    /**
     * Assert that success message is displayed after Website has been created
     *
     * @param StoreIndex $storeIndex
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $storeIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Website success create message is present.';
    }
}
