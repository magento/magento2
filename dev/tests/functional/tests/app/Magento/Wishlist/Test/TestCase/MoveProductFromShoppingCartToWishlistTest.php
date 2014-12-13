<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Checkout\Test\Constraint\AssertAddedProductToCartSuccessMessage;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Fixture\FixtureInterface;

/**
 * Test Creation for Move Product from ShoppingCart to Wishlist
 *
 * Test Flow:
 *
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
    /**
     * Prepare data for test
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
     * Run Move from ShoppingCard to Wishlist test
     *
     * @param CustomerInjectable $customer
     * @param string $product
     * @param AssertAddedProductToCartSuccessMessage $assertAddedProductToCartSuccessMessage
     * @param CheckoutCart $checkoutCart
     * @return array
     */
    public function test(
        CustomerInjectable $customer,
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
