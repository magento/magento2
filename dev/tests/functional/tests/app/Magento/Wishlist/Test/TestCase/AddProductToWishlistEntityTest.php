<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Customer is registered
 * 2. Product is created
 *
 * Steps:
 * 1. Login as a customer
 * 2. Navigate to catalog page
 * 3. Add created product to Wishlist according to dataset
 * 4. Perform all assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-29045
 */
class AddProductToWishlistEntityTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Prepare data for test
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
     * Run Add Product To Wishlist test
     *
     * @param Customer $customer
     * @param string $product
     * @return array
     */
    public function test(Customer $customer, $product)
    {
        $product = $this->createProducts($product)[0];

        // Steps:
        $this->loginCustomer($customer);
        $this->addToWishlist([$product], true);

        return ['product' => $product];
    }
}
