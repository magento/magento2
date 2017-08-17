<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create customer and login to frontend
 * 2. Create products
 * 3. Add products to customer's wishlist
 *
 * Steps:
 * 1. Navigate to My Account -> My Wishlist
 * 2. Fill qty and update wish list
 * 3. Click "Add to Cart"
 * 4. Perform asserts
 *
 * @group Wishlist
 * @ZephyrId MAGETWO-25268
 */
class AddProductsToCartFromCustomerWishlistOnFrontendTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    const STABLE = 'no';
    /* end tags */

    /**
     * Run suggest searching result test.
     *
     * @param Customer $customer
     * @param string $products
     * @param int $qty
     * @param bool $toUpdate
     * @return array
     */
    public function test(Customer $customer, $products, $qty, $toUpdate = true, $toConfigure = false)
    {
        // Preconditions
        $customer->persist();
        $this->loginCustomer($customer);
        $products = $this->createProducts($products);
        $this->addToWishlist($products, $toConfigure);

        // Steps
        $this->addToCart($products, $qty, $toUpdate);

        // Prepare data for asserts
        $cart = $this->createCart($products);

        return ['products' => $products, 'customer' => $customer, 'cart' => $cart];
    }

    /**
     * Add products from wish list to cart.
     *
     * @param array $products
     * @param int $qty
     * @param bool $toUpdate
     * @return void
     */
    protected function addToCart(array $products, $qty, $toUpdate)
    {
        $productBlock = $this->wishlistIndex->getWishlistBlock()->getProductItemsBlock();
        foreach ($products as $product) {
            $this->cmsIndex->getLinksBlock()->openLink("My Wish List");
            $this->cmsIndex->getCmsPageBlock()->waitPageInit();
            if ($qty != '-') {
                $productBlock->getItemProduct($product)->fillProduct(['qty' => $qty]);
                if ($toUpdate) {
                    $this->wishlistIndex->getWishlistBlock()->clickUpdateWishlist();
                }
            }
            $productBlock->getItemProduct($product)->clickAddToCart();
            $this->cmsIndex->getCmsPageBlock()->waitPageInit();
            if (!$this->wishlistIndex->getWishlistBlock()->isVisible()) {
                $this->catalogProductView->getViewBlock()->addToCart($product);
                $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
            }
        }
    }

    /**
     * Create cart fixture.
     *
     * @param array $products
     * @return Cart
     */
    protected function createCart(array $products)
    {
        return $this->fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => $products]]]);
    }
}
