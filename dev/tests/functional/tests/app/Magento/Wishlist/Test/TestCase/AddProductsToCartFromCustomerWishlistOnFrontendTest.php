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

use Mtf\ObjectManager;
use Mtf\Client\Browser;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Customer\Test\Fixture\CustomerInjectable;

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
