<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
 * 1. Create customer
 * 2. Create product
 * 3. Login to frontend as a customer
 * 4. Add product to Wish List
 *
 * Steps:
 * 1. Go to Backend
 * 2. Go to Customers > All Customers
 * 3. Open the customer
 * 4. Open wishlist tab
 * 5. Click 'Delete'
 * 6. Perform assertions
 *
 * @group Wishlist
 * @ZephyrId MAGETWO-27813
 */
class DeleteProductFromCustomerWishlistOnBackendTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Prepare data
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
     * Delete product from customer wishlist on backend
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
        //Preconditions
        $product = $this->createProducts($product)[0];
        $this->loginCustomer($customer);
        $this->addToWishlist([$product]);

        //Steps
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerForm = $customerIndexEdit->getCustomerForm();
        $customerForm->openTab('wishlist');
        $filter = ['product_name' => $product->getName()];
        $customerForm->getTab('wishlist')->getSearchGridBlock()->searchAndAction($filter, 'Delete');

        return ['products' => [$product]];
    }
}
