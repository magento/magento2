<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions
 * 1. Test products are created
 *
 * Steps:
 * 1. Add product(s) to Shopping Cart
 * 2. Click 'Remove item' button from Shopping Cart for each product(s)
 * 3. Perform all asserts
 *
 * @group Shopping_Cart_(CS)
 * @ZephyrId MAGETWO-25218
 */
class DeleteProductsFromShoppingCartTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Browser interface
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Checkout cart page
     *
     * @var CheckoutCart
     */
    protected $cartPage;

    /**
     * Prepare test data
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $cartPage
     * @return void
     */
    public function __prepare(
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView,
        CheckoutCart $cartPage
    ) {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductView = $catalogProductView;
        $this->cartPage = $cartPage;
    }

    /**
     * Run test add products to shopping cart
     *
     * @param string $productsData
     * @return void
     */
    public function test($productsData)
    {
        // Preconditions
        $products = $this->prepareProducts($productsData);

        // Steps
        $this->addToCart($products);
        $this->removeProducts($products);
    }

    /**
     * Create products
     *
     * @param string $productList
     * @return InjectableFixture[]
     */
    protected function prepareProducts($productList)
    {
        $createProductsStep = ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $productList]
        );

        $result = $createProductsStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Remove products form cart
     *
     * @param array $products
     * @return void
     */
    protected function removeProducts(array $products)
    {
        $this->cartPage->open();
        foreach ($products as $product) {
            $this->cartPage->getCartBlock()->getCartItem($product)->removeItem();
        }
    }
}
