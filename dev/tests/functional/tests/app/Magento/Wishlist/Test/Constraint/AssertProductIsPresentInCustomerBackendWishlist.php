<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertProductIsPresentInCustomerBackendWishlist
 * Assert that product added to wishlist is present on Customers account on backend
 * - in section Customer Activities - Wishlist
 */
class AssertProductIsPresentInCustomerBackendWishlist extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that products added to wishlist are present on Customers account on backend.
     *
     * @param CustomerIndex $customerIndex
     * @param CustomerInjectable $customer
     * @param CustomerIndexEdit $customerIndexEdit
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CustomerIndex $customerIndex,
        CustomerInjectable $customer,
        CustomerIndexEdit $customerIndexEdit,
        InjectableFixture $product
    ) {
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerIndexEdit->getCustomerForm()->openTab('wishlist');
        /** @var \Magento\Wishlist\Test\Block\Adminhtml\Customer\Edit\Tab\Wishlist\Grid $wishlistGrid */
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTabElement('wishlist')->getSearchGridBlock();

        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistGrid->isRowVisible(['product_name' => $product->getName()], true, false),
            $product->getName() . " is not visible in customer wishlist on backend."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Product is visible in customer wishlist on backend.";
    }
}
