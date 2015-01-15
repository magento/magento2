<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteSuccessSaveMessage
 * Assert that after Website save successful message appears
 */
class AssertWebsiteSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Success website create message
     */
    const SUCCESS_MESSAGE = 'The website has been saved.';

    /**
     * Assert that success message is displayed after Website has been created
     *
     * @param StoreIndex $storeIndex
     * @return void
     */
    public function processAssert(StoreIndex $storeIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $storeIndex->getMessagesBlock()->getSuccessMessages(),
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
