<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 * 3. Open product on frontend.
 *
 * Steps:
 * 1. Login in to Backend.
 * 2. Open Customers > All Customers.
 * 3. Search and open customer from preconditions.
 * 4. Click Create Order.
 * 5. Check product in Recently Viewed Products section.
 * 6. Click Update Changes.
 * 7. Click Configure.
 * 8. Fill data from dataset.
 * 9. Click OK.
 * 10. Click Update Items and Qty's button.
 * 11. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-29723
 */
class MoveRecentlyViewedProductsOnOrderPageTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
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
        $this->objectManager
            ->create('Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep', ['customer' => $customer])
            ->run();

        return ['customer' => $customer];
    }

    /**
     * Runs Move Recently Viewed Products On Order Page.
     *
     * @param Customer $customer
     * @param string $products
     * @return array
     */
    public function test(Customer $customer, $products)
    {
        // Preconditions
        $products = $this->objectManager
            ->create('Magento\Catalog\Test\TestStep\CreateProductsStep', ['products' => $products])
            ->run()['products'];
        $this->objectManager
            ->create('Magento\Catalog\Test\TestStep\OpenProductsOnFrontendStep', ['products' => $products])
            ->run();

        // Steps
        $this->objectManager
            ->create('Magento\Customer\Test\TestStep\OpenCustomerOnBackendStep', ['customer' => $customer])
            ->run();
        $this->objectManager->create('Magento\Customer\Test\TestStep\CreateOrderFromCustomerAccountStep')->run();
        $this->objectManager->create('Magento\Sales\Test\TestStep\SelectStoreStep')->run();
        $this->objectManager
            ->create('Magento\Sales\Test\TestStep\AddRecentlyViewedProductsToCartStep', ['products' => $products])
            ->run();
        $this->objectManager
            ->create('Magento\Sales\Test\TestStep\ConfigureProductsStep', ['products' => $products])
            ->run();

        return ['products' => $products];
    }
}
