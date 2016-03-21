<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Asserts that "Back" button works on customer edit page.
 */
class AssertCustomerBackendBackButton extends AbstractConstraint
{
    /**
     * Asserts that "Back" button works on customer edit page (returns to customers grid).
     *
     * @param CustomerIndexEdit $customerEditPage
     * @param CustomerIndex $customerGridPage
     * @return void
     */
    public function processAssert(CustomerIndexEdit $customerEditPage, CustomerIndex $customerGridPage)
    {
        $customerEditPage->getPageActionsBlock()->back();
        \PHPUnit_Framework_Assert::assertTrue(
            $customerGridPage->getCustomerGridBlock()->isVisible(),
            'Clicking on "Back" button does not redirect to customers grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return '"Back" button on customer edit page redirects to customers grid.';
    }
}
