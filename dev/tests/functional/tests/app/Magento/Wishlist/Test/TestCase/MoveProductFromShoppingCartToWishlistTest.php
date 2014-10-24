<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
