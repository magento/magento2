<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Test Creation for ConfigureProductInCustomerWishlist on frontend
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create composite products
 * 3. Log in to frontend
 * 4. Add products to the customer's wish list (unconfigured)
 *
 * Steps:
 * 1. Open Wish list
 * 2. Click 'Configure' for the product
 * 3. Fill data
 * 4. Click 'Ok'
 * 5. Perform assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-29507
 */
class ConfigureProductInCustomerWishlistOnFrontendTest extends AbstractWishlistTest
{
    /**
     * Prepare data
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Configure customer wish list on frontend
     *
     * @param CustomerInjectable $customer
     * @param string $product
     * @return array
     */
    public function test(CustomerInjectable $customer, $product)
    {
        $this->markTestIncomplete("Bug: MAGETWO-30097");
        // Preconditions
        $product = $this->createProducts($product)[0];
        $this->loginCustomer($customer);
        $this->addToWishlist([$product]);

        // Steps
        $this->cmsIndex->getLinksBlock()->openLink('My Wish List');
        $this->wishlistIndex->getItemsBlock()->getItemProduct($product)->clickEdit();
        $this->catalogProductView->getViewBlock()->addToWishlist($product);

        return ['product' => $product];
    }
}
