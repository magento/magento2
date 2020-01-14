<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that products added to wishlist is present on Customers account on backend in Customer Activities - Wishlist.
 */
class AssertProductsIsPresentInCustomerBackendWishlist extends AbstractConstraint
{
    /**
     * Assert that products added to wishlist is present
     * on Customers account on backend in Customer Activities - Wishlist.
     *
     * @param Customer $customer
     * @param CustomerIndexEdit $customerIndexEdit
     * @param array $products
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerIndexEdit $customerIndexEdit,
        array $products
    ) {
        $customerIndexEdit->open(['id' => $customer->getId()]);
        $customerIndexEdit->getCustomerForm()->openTab('wishlist');
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTab('wishlist')->getSearchGridBlock();
        foreach ($products as $product) {
            \PHPUnit\Framework\Assert::assertTrue(
                $wishlistGrid->isRowVisible(['product_name' => $product->getName()]),
                $product->getName() . " is not visible in customer wishlist on backend."
            );
        }
    }

    /**
     * Assert success message that products added to wishlist is present
     * on Customers account on backend in Customer Activities - Wishlist.
     *
     * @return string
     */
    public function toString()
    {
        return "Products is visible in customer wishlist on backend in Customer Activities - Wishlist.";
    }
}
