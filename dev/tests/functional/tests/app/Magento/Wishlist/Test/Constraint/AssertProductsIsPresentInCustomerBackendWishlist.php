<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that products added to wishlist is present on Customers account on backend in Customer Activities - Wishlist.
 */
class AssertProductsIsPresentInCustomerBackendWishlist extends AbstractConstraint
{
    /**
     * Assert that products added to wishlist is present
     * on Customers account on backend in Customer Activities - Wishlist.
     *
     * @param BrowserInterface $browser
     * @param Customer $customer
     * @param CustomerIndexEdit $customerIndexEdit
     * @param array $products
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        Customer $customer,
        CustomerIndexEdit $customerIndexEdit,
        array $products
    ) {
        $browser->open($_ENV['app_backend_url'] . 'customer/index/edit/id/' . $customer->getId());
        $customerIndexEdit->getCustomerForm()->openTab('wishlist');
        $wishlistGrid = $customerIndexEdit->getCustomerForm()->getTab('wishlist')->getSearchGridBlock();
        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertTrue(
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
