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
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. All type products is created
 *
 * Steps:
 * 1. Navigate to frontend
 * 2. Open test product page
 * 3. Add to cart test product
 * 4. Perform all asserts
 *
 * @group Shopping_Cart
 * @ZephyrId MAGETWO-25382, MAGETWO-42677
 */
class AddProductsToShoppingCartEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S0';
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
     * Configuration data.
     *
     * @var string
     */
    private $configData;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Should cache be flushed.
     *
     * @var bool
     */
    private $flushCache;

    /**
     * Prepare test data.
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $cartPage
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __prepare(
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView,
        CheckoutCart $cartPage,
        TestStepFactory $testStepFactory
    ) {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductView = $catalogProductView;
        $this->cartPage = $cartPage;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Run test add products to shopping cart.
     *
     * @param array $productsData
     * @param array $cart
     * @param string|null $configData [optional]
     * @param bool $flushCache [optional]
     * @return array
     */
    public function test(array $productsData, array $cart, $configData = null, $flushCache = false)
    {
        // Preconditions
        $this->configData = $configData;
        $this->flushCache = $flushCache;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => $this->flushCache]
        )->run();
        $products = $this->prepareProducts($productsData);

        // Steps
        $this->addToCart($products);

        $cart['data']['items'] = ['products' => $products];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }

    /**
     * Create products.
     *
     * @param array $productList
     * @return array
     */
    protected function prepareProducts(array $productList)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $addToCartStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart.
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
