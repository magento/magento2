<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Test Creation for DeleteProductsFromWishlist
 *
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
    /**
     * Delete products form default wish list
     *
     * @param CustomerInjectable $customer
     * @param string $products
     * @param string $removedProductsIndex [optional]
     * @return array
     */
    public function test(CustomerInjectable $customer, $products, $removedProductsIndex = null)
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
