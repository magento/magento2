<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Customer registered
 * 2. Products are created
 *
 * Steps:
 * 1. Login as customer
 * 2. Add products to Wishlist
 * 3. Navigate to My Account -> My Wishlist
 * 4. Click "Remove item"
 * 5. Perform all assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-28874
 */
class DeleteProductsFromWishlistOnFrontendTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Delete products form default wish list
     *
     * @param Customer $customer
     * @param string $products
     * @param string $removedProductsIndex [optional]
     * @return array
     */
    public function test(Customer $customer, $products, $removedProductsIndex = null)
    {
        // Preconditions
        $customer->persist();
        $this->loginCustomer($customer);
        $products = $this->createProducts($products);
        $this->addToWishlist($products);

        // Steps
        $this->cmsIndex->getLinksBlock()->openLink("My Wish List");
        $removeProducts = $this->removeProducts($products, $removedProductsIndex);

        return ['products' => $removeProducts, 'customer' => $customer];
    }

    /**
     * Remove products from wish list
     *
     * @param array $products
     * @param string $removedProductsIndex
     * @return array
     */
    protected function removeProducts(array $products, $removedProductsIndex)
    {
        $removeProducts = [];
        if ($removedProductsIndex) {
            $removedProductsIndex = explode(',', $removedProductsIndex);
            foreach ($removedProductsIndex as $index) {
                $this->wishlistIndex->getItemsBlock()->getItemProduct($products[--$index])->remove();
                $removeProducts[] = $products[$index];
            }
        } else {
            $this->wishlistIndex->getItemsBlock()->removeAllProducts();
            $removeProducts = $products;
        }

        return $removeProducts;
    }
}
