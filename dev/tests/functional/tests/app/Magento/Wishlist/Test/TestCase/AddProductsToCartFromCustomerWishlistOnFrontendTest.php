<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Client\Browser;

/**
 * Test Creation for Adding products from Wishlist to Cart
 *
 * Test Flow:
 *
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
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-25268
 */
class AddProductsToCartFromCustomerWishlistOnFrontendTest extends AbstractWishlistTest
{
    /**
     * Browser
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Run suggest searching result test
     *
     * @param CustomerInjectable $customer
     * @param string $products
     * @param int $qty
     * @param Browser $browser
     * @return array
     */
    public function test(CustomerInjectable $customer, $products, $qty, Browser $browser)
    {
        $this->markTestIncomplete("Bug: MAGETWO-30097");
        // Preconditions
        $this->browser = $browser;
        $customer->persist();
        $this->loginCustomer($customer);
        $products = $this->createProducts($products);
        $this->addToWishlist($products);

        // Steps
        $this->addToCart($products, $qty);

        // Prepare data for asserts
        $cart = $this->createCart($products);

        return ['products' => $products, 'customer' => $customer, 'cart' => $cart];
    }

    /**
     * Add products from wish list to cart
     *
     * @param array $products
     * @param int $qty
     * @return void
     */
    protected function addToCart(array $products, $qty)
    {
        foreach ($products as $product) {
            $this->cmsIndex->getLinksBlock()->openLink("My Wish List");
            if ($qty != '-') {
                $this->wishlistIndex->getItemsBlock()->getItemProduct($product)->fillProduct(['qty' => $qty]);
                $this->wishlistIndex->getWishlistBlock()->clickUpdateWishlist();
            }
            $this->wishlistIndex->getItemsBlock()->getItemProduct($product)->clickAddToCart();
            if (strpos($this->browser->getUrl(), 'checkout/cart/') === false) {
                $this->catalogProductView->getViewBlock()->addToCart($product);
            }
        }
    }

    /**
     * Create cart fixture
     *
     * @param array $products
     * @return Cart
     */
    protected function createCart(array $products)
    {
        return $this->fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => $products]]]);
    }
}
