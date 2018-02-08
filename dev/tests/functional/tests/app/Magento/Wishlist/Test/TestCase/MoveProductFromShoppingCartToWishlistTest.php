<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Checkout\Test\Constraint\AssertAddedProductToCartSuccessMessage;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Preconditions:
 * 1. Test products are created.
 *
 * Steps:
 * 1. Add product to Shopping Cart.
 * 2. Call AssertAddProductToCartSuccessMessage.
 * 2. Click 'Move to Wishlist' button from Shopping Cart for added product.
 * 3. Perform asserts.
 *
 * @group Shopping_Cart_(CS)
 * @ZephyrId MAGETWO-29545
 */
class MoveProductFromShoppingCartToWishlistTest extends AbstractWishlistTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'extended_acceptance_test';
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
     * Run Move from ShoppingCard to Wishlist test
     *
     * @param Customer $customer
     * @param string $product
     * @param AssertAddedProductToCartSuccessMessage $assertAddedProductToCartSuccessMessage
     * @param CheckoutCart $checkoutCart
     * @return array
     */
    public function test(
        Customer $customer,
        $product,
        AssertAddedProductToCartSuccessMessage $assertAddedProductToCartSuccessMessage,
        CheckoutCart $checkoutCart
    ) {
        // Preconditions:
        $product = $this->createProducts($product)[0];
        $this->loginCustomer($customer);

        // Steps:
        $this->addToCart($product);
        $assertAddedProductToCartSuccessMessage->processAssert($checkoutCart, $product);
        $checkoutCart->open();
        $checkoutCart->getCartBlock()->getCartItem($product)->moveToWishlist();

        return ['product' => $product];
    }

    /**
     * Add product to cart
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function addToCart(FixtureInterface $product)
    {
        $addProductsToTheCartStep = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => [$product]]
        );
        $addProductsToTheCartStep->run();
    }
}
