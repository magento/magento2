<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\StoreNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that 'Delete' button on Store view edit page is absent.
 */
class AssertStoreNoDeleteButton extends AbstractConstraint
{
    /**
     * Assert that 'Delete' button on Store view edit page is absent.
     *
     * @param StoreNew $storePage
     * @return void
     */
    public function processAssert(StoreNew $storePage)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $storePage->getFormPageActions()->checkDeleteButton(),
            '\'Delete\' button on Store view edit page is present when it should not.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '\'Delete\' button on Store view edit page is absent.';
    }
}
