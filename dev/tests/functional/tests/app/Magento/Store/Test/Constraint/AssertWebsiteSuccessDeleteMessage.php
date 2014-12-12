<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWebsiteSuccessDeleteMessage
 * Assert that after website delete successful message appears
 */
class AssertWebsiteSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Success website delete message
     */
    const SUCCESS_DELETE_MESSAGE = 'The website has been deleted.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        return 'Website success delete message is present.';
    }
}
