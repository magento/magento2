<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Preconditions:
 * 1. Create customer
 * 2. Create products
 * 3. Add products to the customer's wishlist (unconfigured)
 *
 * Steps:
 * 1. Go to Backend
 * 2. Go to Customers > All Customers
 * 3. Open the customer
 * 4. Open wishlist tab
 * 5. Click 'Configure' for the product
 * 6. Fill in data
 * 7. Click Ok
 * 8. Perform assertions
 *
 * @group Wishlist
 * @ZephyrId MAGETWO-29257
 */
class ConfigureProductInCustomerWishlistOnBackendTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Create customer.
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
        $this->addToWishlist([$product]);
        // Steps
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerForm = $customerIndexEdit->getCustomerForm();
        $customerForm->openTab('wishlist');
        $customerForm->getTab('wishlist')->getSearchGridBlock()->searchAndAction(
            ['product_name' => $product->getName()],
            'Configure'
        );
        $customerIndexEdit->getConfigureProductBlock()->configProduct($product);

        return['product' => $product];
    }
}
