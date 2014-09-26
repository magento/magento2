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
use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

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
class AddProductsToCartFromCustomerWishlistOnFrontendTest extends Injectable
{
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Browser
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Wishlist index page
     *
     * @var WishlistIndex
     */
    protected $wishlistIndex;

    /**
     * Injection data
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param FixtureFactory $fixtureFactory
     * @param Browser $browser
     * @param WishlistIndex $wishlistIndex
     * @param ObjectManager $objectManager
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        FixtureFactory $fixtureFactory,
        Browser $browser,
        WishlistIndex $wishlistIndex,
        ObjectManager $objectManager
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->fixtureFactory = $fixtureFactory;
        $this->browser = $browser;
        $this->wishlistIndex = $wishlistIndex;
        $this->objectManager = $objectManager;
    }

    /**
     * Run suggest searching result test
     *
     * @param CustomerInjectable $customer
     * @param string $products
     * @param int $qty
     * @return array
     */
    public function test(CustomerInjectable $customer, $products, $qty)
    {
        // Preconditions
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
     * Login customer
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomer(CustomerInjectable $customer)
    {
        $loginCustomerOnFrontendStep = $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $loginCustomerOnFrontendStep->run();
    }

    /**
     * Create products
     *
     * @param string $products
     * @return array
     */
    protected function createProducts($products)
    {
        $createProductsStep = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Add products to wish list
     *
     * @param array $products
     * @return void
     */
    protected function addToWishlist(array $products)
    {
        foreach ($products as $product) {
            $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->addToWishlist();
        }
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
                $this->wishlistIndex->getItemsBlock()->getItemProductByName($product->getName())
                    ->fillProduct(['qty' => $qty]);
                $this->wishlistIndex->getWishlistBlock()->clickUpdateWishlist();
            }
            $this->wishlistIndex->getItemsBlock()->getItemProductByName($product->getName())->clickAddToCart();
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
