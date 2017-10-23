<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer.
 * 2. Create products from dataset.
 * 3. Add products to the customer's wish list (composite products should be configured).
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Go to Customers > All Customers.
 * 3. Search and open customer.
 * 4. Open wish list tab.
 * 5. Perform assertions.
 *
 * @group Wishlist
 * @ZephyrId MAGETWO-29616
 */
class ViewProductInCustomerWishlistOnBackendTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Prepare customer for test.
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Configure customer wish list on backend.
     *
     * @param Customer $customer
     * @param string $product
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @return array
     */
    public function test(
        Customer $customer,
        $product,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit
    ) {
        // Preconditions
        $product = $this->createProducts($product)[0];
        $this->loginCustomer($customer);
        $this->addToWishlist([$product], true);

        // Steps
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerIndexEdit->getCustomerForm()->openTab('wishlist');

        return['product' => $product];
    }
}
